<?php
namespace App\Http\Controllers;

use App\Models\JobPosting;
use App\Models\Applicant;
use App\Models\Interview;
use App\Models\OfferLetter;
use App\Models\Department;
use App\Models\Designation;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class RecruitmentController extends Controller
{
    // ── DASHBOARD ────────────────────────────────────────────
    public function index()
    {
        $companyId = auth()->user()->company_id;

        $stats = [
            'open_jobs'    => JobPosting::where('company_id', $companyId)->where('status','open')->count(),
            'total_apps'   => Applicant::where('company_id', $companyId)->count(),
            'interviews'   => Interview::whereHas('jobPosting', fn($q) => $q->where('company_id', $companyId))
                                ->where('status','scheduled')
                                ->where('scheduled_at','>=', now())->count(),
            'hired_month'  => Applicant::where('company_id', $companyId)
                                ->where('stage','hired')
                                ->whereMonth('updated_at', now()->month)->count(),
        ];

        $jobs = JobPosting::with(['department','applicants'])
            ->where('company_id', $companyId)
            ->whereIn('status',['open','on_hold'])
            ->withCount('applicants')
            ->latest()->get();

        $recentApplicants = Applicant::with(['jobPosting'])
            ->where('company_id', $companyId)
            ->latest()->take(8)->get();

        $upcomingInterviews = Interview::with(['applicant','jobPosting'])
            ->whereHas('jobPosting', fn($q) => $q->where('company_id', $companyId))
            ->where('status','scheduled')
            ->where('scheduled_at','>=', now())
            ->orderBy('scheduled_at')
            ->take(5)->get();

        $pipeline = [
            'applied'             => Applicant::where('company_id',$companyId)->where('stage','applied')->count(),
            'screening'           => Applicant::where('company_id',$companyId)->where('stage','screening')->count(),
            'shortlisted'         => Applicant::where('company_id',$companyId)->where('stage','shortlisted')->count(),
            'interview_scheduled' => Applicant::where('company_id',$companyId)->where('stage','interview_scheduled')->count(),
            'offer_sent'          => Applicant::where('company_id',$companyId)->where('stage','offer_sent')->count(),
            'hired'               => Applicant::where('company_id',$companyId)->where('stage','hired')->count(),
        ];

        return view('recruitment.index', compact(
            'stats','jobs','recentApplicants','upcomingInterviews','pipeline'
        ));
    }

    // ── JOB POSTINGS LIST ────────────────────────────────────
    public function jobs(Request $request)
    {
        $companyId = auth()->user()->company_id;

        $query = JobPosting::with(['department','designation'])
            ->withCount('applicants')
            ->where('company_id', $companyId);

        if ($request->filled('status'))     $query->where('status', $request->status);
        if ($request->filled('department')) $query->where('department_id', $request->department);
        if ($request->filled('search')) {
            $query->where('title','like',"%{$request->search}%")
                  ->orWhere('reference_no','like',"%{$request->search}%");
        }

        $jobs        = $query->latest()->paginate(12)->withQueryString();
        $departments = Department::where('company_id', $companyId)->where('is_active',true)->get();

        return view('recruitment.jobs', compact('jobs','departments'));
    }

    // ── CREATE JOB ───────────────────────────────────────────
    public function createJob()
    {
        $departments  = Department::where('company_id', auth()->user()->company_id)->where('is_active',true)->get();
        $designations = Designation::where('company_id', auth()->user()->company_id)->where('is_active',true)->get();
        return view('recruitment.create-job', compact('departments','designations'));
    }

    // ── STORE JOB ────────────────────────────────────────────
    public function storeJob(Request $request)
    {
        $request->validate([
            'title'            => 'required|string|max:150',
            'department_id'    => 'nullable|exists:departments,id',
            'designation_id'   => 'nullable|exists:designations,id',
            'type'             => 'required|string',
            'experience_level' => 'required|string',
            'vacancies'        => 'required|integer|min:1',
            'deadline'         => 'nullable|date|after:today',
            'description'      => 'nullable|string',
            'requirements'     => 'nullable|string',
            'responsibilities' => 'nullable|string',
            'salary_min'       => 'nullable|numeric|min:0',
            'salary_max'       => 'nullable|numeric|min:0|gte:salary_min',
            'location'         => 'nullable|string|max:100',
            'status'           => 'required|in:draft,open',
        ]);

        $job = JobPosting::create([
            'company_id'       => auth()->user()->company_id,
            'created_by'       => auth()->id(),
            'reference_no'     => 'JOB-' . strtoupper(Str::random(6)),
            'posted_date'      => $request->status === 'open' ? now() : null,
            ...$request->only([
                'title','department_id','designation_id','type','experience_level',
                'vacancies','deadline','description','requirements','responsibilities',
                'salary_min','salary_max','salary_disclosed','location','status',
            ]),
        ]);

        AuditLog::log('job_posting_created', $job);
        return redirect()->route('recruitment.jobs')
            ->with('success', "Job posting \"{$job->title}\" created.");
    }

    // ── JOB DETAIL + KANBAN ──────────────────────────────────
    public function showJob(JobPosting $jobPosting)
    {
        $this->authorizeJob($jobPosting);

        $jobPosting->load(['department','designation','creator']);

        $applicantsByStage = Applicant::where('job_posting_id', $jobPosting->id)
            ->with(['interviews'])
            ->get()
            ->groupBy('stage');

        $stages = [
            'applied','screening','shortlisted','interview_scheduled',
            'interviewed','assessment','offer_sent','offer_accepted','hired',
        ];

        return view('recruitment.show-job', compact('jobPosting','applicantsByStage','stages'));
    }

    // ── UPDATE JOB STATUS ────────────────────────────────────
    public function updateJobStatus(Request $request, JobPosting $jobPosting)
    {
        $this->authorizeJob($jobPosting);
        $request->validate(['status' => 'required|in:draft,open,on_hold,closed,cancelled']);
        $jobPosting->update([
            'status'      => $request->status,
            'posted_date' => $request->status === 'open' && !$jobPosting->posted_date ? now() : $jobPosting->posted_date,
        ]);
        return back()->with('success', 'Job status updated.');
    }

    // ── ADD APPLICANT ────────────────────────────────────────
    public function storeApplicant(Request $request, JobPosting $jobPosting)
    {
        $this->authorizeJob($jobPosting);

        $request->validate([
            'first_name'             => 'required|string|max:100',
            'last_name'              => 'required|string|max:100',
            'email'                  => 'required|email',
            'phone'                  => 'nullable|string|max:20',
            'total_experience_years' => 'nullable|integer|min:0',
            'current_employer'       => 'nullable|string|max:150',
            'expected_salary'        => 'nullable|numeric|min:0',
            'notice_period_days'     => 'nullable|integer|min:0',
            'city'                   => 'nullable|string|max:100',
            'source'                 => 'nullable|string|max:100',
            'cv'                     => 'nullable|file|mimes:pdf,doc,docx|max:5120',
        ]);

        $cvPath = null;
        if ($request->hasFile('cv')) {
            $cvPath = $request->file('cv')->store("cvs/{$jobPosting->id}", 'public');
        }

        $applicant = Applicant::create([
            'company_id'             => auth()->user()->company_id,
            'job_posting_id'         => $jobPosting->id,
            'cv_path'                => $cvPath,
            ...$request->only([
                'first_name','last_name','email','phone','cnic',
                'total_experience_years','current_employer','current_designation',
                'current_salary','expected_salary','notice_period_days',
                'city','source','referred_by','notes',
            ]),
        ]);

        $jobPosting->increment('total_applications');
        AuditLog::log('applicant_added', $applicant);

        return back()->with('success', "Applicant {$applicant->full_name} added.");
    }

    // ── SHOW APPLICANT PROFILE ───────────────────────────────
    public function showApplicant(Applicant $applicant)
    {
        $applicant->load(['jobPosting.department','interviews','offerLetters.creator']);
        return view('recruitment.applicant', compact('applicant'));
    }

    // ── MOVE STAGE ───────────────────────────────────────────
    public function moveStage(Request $request, Applicant $applicant)
    {
        $request->validate([
            'stage' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        $applicant->update([
            'stage' => $request->stage,
            'notes' => $request->notes ?? $applicant->notes,
        ]);

        AuditLog::log('applicant_stage_changed', $applicant, ['stage' => $applicant->getOriginal('stage')], ['stage' => $request->stage]);
        return back()->with('success', "Moved to: " . ucfirst(str_replace('_',' ',$request->stage)));
    }

    // ── RATE APPLICANT ───────────────────────────────────────
    public function rateApplicant(Request $request, Applicant $applicant)
    {
        $request->validate(['rating' => 'required|integer|min:1|max:5']);
        $applicant->update(['rating' => $request->rating]);
        return back()->with('success', 'Rating saved.');
    }

    // ── SCHEDULE INTERVIEW ───────────────────────────────────
    public function scheduleInterview(Request $request, Applicant $applicant)
    {
        $request->validate([
            'round'        => 'required|integer|min:1',
            'type'         => 'required|string',
            'scheduled_at' => 'required|date|after:now',
            'duration_minutes' => 'nullable|integer|min:15',
            'location'     => 'nullable|string|max:255',
        ]);

        $interview = Interview::create([
            'applicant_id'     => $applicant->id,
            'job_posting_id'   => $applicant->job_posting_id,
            'scheduled_by'     => auth()->id(),
            'round'            => $request->round,
            'type'             => $request->type,
            'scheduled_at'     => $request->scheduled_at,
            'duration_minutes' => $request->duration_minutes ?? 60,
            'location'         => $request->location,
            'status'           => 'scheduled',
        ]);

        $applicant->update(['stage' => 'interview_scheduled']);

        AuditLog::log('interview_scheduled', $interview);
        return back()->with('success', 'Interview scheduled successfully.');
    }

    // ── RECORD INTERVIEW RESULT ──────────────────────────────
    public function recordInterview(Request $request, Interview $interview)
    {
        $request->validate([
            'status'         => 'required|in:completed,cancelled,no_show',
            'score'          => 'nullable|integer|min:1|max:10',
            'feedback'       => 'nullable|string',
            'recommendation' => 'nullable|in:strong_hire,hire,maybe,no_hire',
        ]);

        $interview->update($request->only(['status','score','feedback','recommendation']));

        if ($request->status === 'completed') {
            $interview->applicant->update(['stage' => 'interviewed']);
        }

        return back()->with('success', 'Interview result recorded.');
    }

    // ── SEND OFFER ───────────────────────────────────────────
    public function sendOffer(Request $request, Applicant $applicant)
    {
        $request->validate([
            'offered_salary' => 'required|numeric|min:1',
            'joining_date'   => 'required|date|after:today',
            'offer_expiry'   => 'required|date|after:today',
            'terms'          => 'nullable|string',
        ]);

        $offer = OfferLetter::create([
            'applicant_id'   => $applicant->id,
            'job_posting_id' => $applicant->job_posting_id,
            'created_by'     => auth()->id(),
            'offer_number'   => 'OFR-' . strtoupper(Str::random(6)),
            'offered_salary' => $request->offered_salary,
            'joining_date'   => $request->joining_date,
            'offer_expiry'   => $request->offer_expiry,
            'terms'          => $request->terms,
            'status'         => 'sent',
        ]);

        $applicant->update(['stage' => 'offer_sent']);
        AuditLog::log('offer_sent', $offer);

        return back()->with('success', "Offer #{$offer->offer_number} sent to {$applicant->full_name}.");
    }

    // ── ACCEPT / DECLINE OFFER ───────────────────────────────
    public function respondOffer(Request $request, OfferLetter $offer)
    {
        $request->validate([
            'response'       => 'required|in:accepted,declined',
            'decline_reason' => 'nullable|string',
        ]);

        if ($request->response === 'accepted') {
            $offer->update(['status' => 'accepted', 'accepted_at' => now()]);
            $offer->applicant->update(['stage' => 'offer_accepted']);
        } else {
            $offer->update([
                'status'         => 'declined',
                'declined_at'    => now(),
                'decline_reason' => $request->decline_reason,
            ]);
            $offer->applicant->update(['stage' => 'offer_declined']);
        }

        return back()->with('success', 'Offer response recorded.');
    }

    // ── MARK AS HIRED ────────────────────────────────────────
    public function markHired(Applicant $applicant)
    {
        $applicant->update(['stage' => 'hired']);
        $applicant->jobPosting->decrement('vacancies');
        AuditLog::log('applicant_hired', $applicant);
        return back()->with('success', "{$applicant->full_name} marked as Hired!");
    }

    private function authorizeJob(JobPosting $job): void {
        if ($job->company_id !== auth()->user()->company_id) abort(403);
    }
}