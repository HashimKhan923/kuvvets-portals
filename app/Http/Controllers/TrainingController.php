<?php
namespace App\Http\Controllers;

use App\Models\TrainingProgram;
use App\Models\TrainingSession;
use App\Models\TrainingEnrollment;
use App\Models\EmployeeCertification;
use App\Models\Skill;
use App\Models\EmployeeSkill;
use App\Models\Employee;
use App\Models\Department;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Carbon\Carbon;

class TrainingController extends Controller
{
    // ── DASHBOARD ────────────────────────────────────────────
    public function index()
    {
        $companyId = auth()->user()->company_id;

        $stats = [
            'total_programs'   => TrainingProgram::where('company_id',$companyId)->where('is_active',true)->count(),
            'upcoming_sessions'=> TrainingSession::where('company_id',$companyId)
                ->where('status','scheduled')->where('start_date','>=',today())->count(),
            'enrolled_this_month' => TrainingEnrollment::whereHas('session',
                fn($q) => $q->where('company_id',$companyId))
                ->whereMonth('created_at', now()->month)->count(),
            'certifications_expiring' => EmployeeCertification::whereHas('employee',
                fn($q) => $q->where('company_id',$companyId))
                ->whereNotNull('expiry_date')
                ->where('expiry_date','>=',today())
                ->where('expiry_date','<=',today()->addDays(30))->count(),
            'completed_this_month' => TrainingEnrollment::whereHas('session',
                fn($q) => $q->where('company_id',$companyId))
                ->where('status','attended')
                ->whereMonth('updated_at',now()->month)->count(),
            'mandatory_programs' => TrainingProgram::where('company_id',$companyId)
                ->where('is_mandatory',true)->count(),
        ];

        $upcomingSessions = TrainingSession::with(['program','enrollments'])
            ->where('company_id',$companyId)
            ->where('status','scheduled')
            ->where('start_date','>=',today())
            ->orderBy('start_date')
            ->take(5)->get();

        $recentEnrollments = TrainingEnrollment::with(['employee','session.program'])
            ->whereHas('session', fn($q) => $q->where('company_id',$companyId))
            ->latest()->take(8)->get();

        $expiringCerts = EmployeeCertification::with(['employee'])
            ->whereHas('employee', fn($q) => $q->where('company_id',$companyId))
            ->whereNotNull('expiry_date')
            ->where('expiry_date','>=',today())
            ->where('expiry_date','<=',today()->addDays(60))
            ->orderBy('expiry_date')
            ->take(6)->get();

        // Category distribution
        $categoryDist = TrainingProgram::where('company_id',$companyId)
            ->selectRaw('category, COUNT(*) as count')
            ->groupBy('category')
            ->pluck('count','category');

        return view('training.index', compact(
            'stats','upcomingSessions','recentEnrollments','expiringCerts','categoryDist'
        ));
    }

    // ── PROGRAMS LIST ────────────────────────────────────────
    public function programs(Request $request)
    {
        $companyId = auth()->user()->company_id;

        $query = TrainingProgram::where('company_id',$companyId)
            ->withCount('sessions');

        if ($request->filled('category'))
            $query->where('category',$request->category);
        if ($request->filled('search'))
            $query->where('title','like',"%{$request->search}%");

        $programs = $query->latest()->get();

        return view('training.programs', compact('programs'));
    }

    // ── CREATE PROGRAM ───────────────────────────────────────
    public function storeProgram(Request $request)
    {
        $request->validate([
            'title'            => 'required|string|max:200',
            'category'         => 'required|string',
            'delivery_method'  => 'required|string',
            'duration_hours'   => 'required|integer|min:1',
            'cost_per_person'  => 'nullable|numeric|min:0',
        ]);

        $program = TrainingProgram::create([
            'company_id'                 => auth()->user()->company_id,
            'created_by'                 => auth()->id(),
            'title'                      => $request->title,
            'code'                       => 'TRN-' . strtoupper(Str::random(5)),
            'description'                => $request->description,
            'objectives'                 => $request->objectives,
            'category'                   => $request->category,
            'delivery_method'            => $request->delivery_method,
            'duration_hours'             => $request->duration_hours,
            'cost_per_person'            => $request->cost_per_person ?? 0,
            'provider'                   => $request->provider,
            'certificate_name'           => $request->certificate_name,
            'certificate_validity_months'=> $request->certificate_validity_months,
            'is_mandatory'               => $request->boolean('is_mandatory'),
            'is_active'                  => true,
        ]);

        AuditLog::log('training_program_created', $program);
        return redirect()->route('training.sessions.create', ['program' => $program->id])
            ->with('success', "Program \"{$program->title}\" created. Now schedule a session.");
    }

    // ── SESSIONS LIST ────────────────────────────────────────
    public function sessions(Request $request)
    {
        $companyId = auth()->user()->company_id;

        $query = TrainingSession::with(['program'])
            ->withCount('enrollments')
            ->where('company_id',$companyId);

        if ($request->filled('status'))
            $query->where('status',$request->status);
        if ($request->filled('program'))
            $query->where('training_program_id',$request->program);

        $sessions  = $query->latest()->paginate(12)->withQueryString();
        $programs  = TrainingProgram::where('company_id',$companyId)
            ->where('is_active',true)->get();

        return view('training.sessions', compact('sessions','programs'));
    }

    // ── CREATE SESSION FORM ──────────────────────────────────
    public function createSession(Request $request)
    {
        $programs = TrainingProgram::where('company_id', auth()->user()->company_id)
            ->where('is_active',true)->get();
        $selectedProgram = $request->filled('program')
            ? TrainingProgram::find($request->program)
            : null;

        return view('training.create-session', compact('programs','selectedProgram'));
    }

    // ── STORE SESSION ────────────────────────────────────────
    public function storeSession(Request $request)
    {
        $request->validate([
            'training_program_id' => 'required|exists:training_programs,id',
            'title'               => 'required|string|max:200',
            'start_date'          => 'required|date',
            'end_date'            => 'required|date|after_or_equal:start_date',
            'start_time'          => 'nullable|date_format:H:i',
            'end_time'            => 'nullable|date_format:H:i',
            'max_participants'    => 'required|integer|min:1',
            'venue'               => 'nullable|string|max:200',
            'trainer_name'        => 'nullable|string|max:150',
        ]);

        $session = TrainingSession::create([
            'company_id'          => auth()->user()->company_id,
            'created_by'          => auth()->id(),
            'session_code'        => 'SES-' . strtoupper(Str::random(6)),
            'training_program_id' => $request->training_program_id,
            'title'               => $request->title,
            'start_date'          => $request->start_date,
            'end_date'            => $request->end_date,
            'start_time'          => $request->start_time,
            'end_time'            => $request->end_time,
            'venue'               => $request->venue,
            'trainer_name'        => $request->trainer_name,
            'trainer_email'       => $request->trainer_email,
            'max_participants'    => $request->max_participants,
            'actual_cost'         => $request->actual_cost ?? 0,
            'notes'               => $request->notes,
            'status'              => 'scheduled',
        ]);

        AuditLog::log('training_session_created', $session);
        return redirect()->route('training.session', $session)
            ->with('success', "Session \"{$session->title}\" scheduled successfully.");
    }

    // ── SESSION DETAIL ───────────────────────────────────────
    public function showSession(TrainingSession $session)
    {
        $this->authorizeSession($session);
        $session->load(['program','enrollments.employee.department','creator']);

        $employees = Employee::where('company_id', auth()->user()->company_id)
            ->where('employment_status','active')
            ->whereNotIn('id', $session->enrollments->pluck('employee_id'))
            ->orderBy('first_name')->get();

        return view('training.session', compact('session','employees'));
    }

    // ── UPDATE SESSION STATUS ────────────────────────────────
    public function updateSessionStatus(Request $request, TrainingSession $session)
    {
        $this->authorizeSession($session);
        $request->validate(['status' => 'required|string']);
        $session->update(['status' => $request->status]);
        return back()->with('success', 'Session status updated.');
    }

    // ── ENROLL EMPLOYEE ──────────────────────────────────────
    public function enroll(Request $request, TrainingSession $session)
    {
        $this->authorizeSession($session);

        $request->validate([
            'employee_ids'   => 'required|array',
            'employee_ids.*' => 'exists:employees,id',
        ]);

        $enrolled = 0;
        foreach ($request->employee_ids as $empId) {
            if (TrainingEnrollment::where('training_session_id',$session->id)
                ->where('employee_id',$empId)->exists()) continue;

            if ($session->isFull()) break;

            TrainingEnrollment::create([
                'training_session_id' => $session->id,
                'employee_id'         => $empId,
                'enrolled_by'         => auth()->id(),
                'status'              => 'enrolled',
            ]);

            $session->increment('enrolled_count');
            $enrolled++;
        }

        return back()->with('success', "{$enrolled} employee(s) enrolled successfully.");
    }

    // ── MARK ATTENDANCE ──────────────────────────────────────
    public function markAttendance(Request $request, TrainingSession $session)
    {
        $this->authorizeSession($session);

        $attended = $request->input('attended', []);
        $scores   = $request->input('scores', []);

        foreach ($session->enrollments as $enrollment) {
            $isAttended = in_array($enrollment->id, $attended);
            $score      = $scores[$enrollment->id] ?? null;

            $enrollment->update([
                'status'          => $isAttended ? 'attended' : 'absent',
                'completed'       => $isAttended,
                'score'           => $score,
                'passed'          => $score ? $score >= 50 : null,
                'completion_date' => $isAttended ? now()->toDateString() : null,
            ]);

            // Auto-create certification if program has certificate
            if ($isAttended && $session->program->certificate_name) {
                $expiry = $session->program->certificate_validity_months
                    ? now()->addMonths($session->program->certificate_validity_months)
                    : null;

                $certNumber = 'CERT-' . strtoupper(Str::random(8));

                EmployeeCertification::firstOrCreate(
                    ['employee_id' => $enrollment->employee_id,
                     'training_enrollment_id' => $enrollment->id],
                    [
                        'certificate_name'   => $session->program->certificate_name,
                        'issued_by'          => $session->trainer_name ?? 'KUVVET HR',
                        'certificate_number' => $certNumber,
                        'issue_date'         => now()->toDateString(),
                        'expiry_date'        => $expiry,
                    ]
                );

                $enrollment->update([
                    'certificate_number' => $certNumber,
                    'certificate_expiry' => $expiry,
                ]);
            }
        }

        // Update session avg rating
        $avgRating = $session->enrollments()
            ->whereNotNull('feedback_rating')
            ->avg('feedback_rating');

        $session->update([
            'status'         => 'completed',
            'average_rating' => $avgRating,
        ]);

        return back()->with('success', 'Attendance marked and certificates generated.');
    }

    // ── CERTIFICATIONS ───────────────────────────────────────
    public function certifications(Request $request)
    {
        $companyId = auth()->user()->company_id;

        $query = EmployeeCertification::with(['employee.department'])
            ->whereHas('employee', fn($q) => $q->where('company_id',$companyId));

        if ($request->filled('status')) {
            match($request->status) {
                'expired'       => $query->where('expiry_date','<',today()),
                'expiring_soon' => $query->whereBetween('expiry_date',[today(),today()->addDays(30)]),
                'valid'         => $query->where(fn($q) =>
                    $q->whereNull('expiry_date')->orWhere('expiry_date','>',today()->addDays(30))),
                default => null,
            };
        }

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('certificate_name','like',"%{$request->search}%")
                  ->orWhereHas('employee',
                      fn($q2) => $q2->where('first_name','like',"%{$request->search}%")
                                    ->orWhere('last_name','like',"%{$request->search}%"));
            });
        }

        $certifications = $query->orderBy('expiry_date')->paginate(15)->withQueryString();

        $stats = [
            'total'         => EmployeeCertification::whereHas('employee',
                fn($q) => $q->where('company_id',$companyId))->count(),
            'valid'         => EmployeeCertification::whereHas('employee',
                fn($q) => $q->where('company_id',$companyId))
                ->where(fn($q) => $q->whereNull('expiry_date')
                    ->orWhere('expiry_date','>',today()->addDays(30)))->count(),
            'expiring_soon' => EmployeeCertification::whereHas('employee',
                fn($q) => $q->where('company_id',$companyId))
                ->whereBetween('expiry_date',[today(),today()->addDays(30)])->count(),
            'expired'       => EmployeeCertification::whereHas('employee',
                fn($q) => $q->where('company_id',$companyId))
                ->where('expiry_date','<',today())->count(),
        ];

        return view('training.certifications', compact('certifications','stats'));
    }

    // ── SKILL MATRIX ─────────────────────────────────────────
    public function skillMatrix(Request $request)
    {
        $companyId   = auth()->user()->company_id;
        $departments = Department::where('company_id',$companyId)->get();
        $skills      = Skill::where('company_id',$companyId)->where('is_active',true)->get();

        $query = Employee::with(['skills.skill','department'])
            ->where('company_id',$companyId)
            ->where('employment_status','active');

        if ($request->filled('department'))
            $query->where('department_id',$request->department);

        $employees = $query->orderBy('first_name')->get();

        return view('training.skill-matrix', compact('employees','skills','departments'));
    }

    // ── UPDATE EMPLOYEE SKILL ────────────────────────────────
    public function updateSkill(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'skill_id'    => 'required|exists:skills,id',
            'level'       => 'required|in:beginner,intermediate,advanced,expert',
            'rating'      => 'required|integer|min:1|max:5',
        ]);

        EmployeeSkill::updateOrCreate(
            ['employee_id' => $request->employee_id,
             'skill_id'    => $request->skill_id],
            ['level'        => $request->level,
             'rating'       => $request->rating,
             'last_assessed'=> today()]
        );

        return response()->json(['success' => true]);
    }

    // ── STORE SKILL ──────────────────────────────────────────
    public function storeSkill(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:100',
            'category' => 'nullable|string|max:100',
        ]);

        Skill::create([
            'company_id'  => auth()->user()->company_id,
            'name'        => $request->name,
            'category'    => $request->category,
            'description' => $request->description,
        ]);

        return back()->with('success', "Skill \"{$request->name}\" added.");
    }

    // ── REPORT ───────────────────────────────────────────────
    public function report(Request $request)
    {
        $companyId = auth()->user()->company_id;

        // Fix: qualify the status column with table name
        $totalHours = TrainingEnrollment::whereHas('session',
            fn($q) => $q->where('company_id', $companyId))
            ->where('training_enrollments.status', 'attended')
            ->join('training_sessions', 'training_sessions.id', '=', 'training_enrollments.training_session_id')
            ->join('training_programs', 'training_programs.id', '=', 'training_sessions.training_program_id')
            ->sum('training_programs.duration_hours');

        $totalCost = TrainingSession::where('company_id', $companyId)
            ->where('training_sessions.status', 'completed')
            ->sum('actual_cost');

        $departments = Department::where('company_id', $companyId)->get();

        $query = Employee::with([
            'trainingEnrollments.session.program',
            'certifications',
            'department',
        ])->where('company_id', $companyId)
        ->where('employment_status', 'active');

        if ($request->filled('department'))
            $query->where('department_id', $request->department);

        $employees = $query->orderBy('first_name')->get();

        $report = $employees->map(function ($emp) {
            $enrollments = $emp->trainingEnrollments;
            $attended    = $enrollments->where('status', 'attended');
            $totalHrs    = $attended->sum(
                fn($e) => $e->session?->program?->duration_hours ?? 0
            );
            $certs        = $emp->certifications;
            $validCerts   = $certs->filter(fn($c) => !$c->isExpired());
            $expiredCerts = $certs->filter(fn($c) => $c->isExpired());

            return [
                'employee'        => $emp,
                'total_trainings' => $enrollments->count(),
                'attended'        => $attended->count(),
                'absent'          => $enrollments->where('status', 'absent')->count(),
                'completion_rate' => $enrollments->count() > 0
                    ? round(($attended->count() / $enrollments->count()) * 100)
                    : 0,
                'total_hours'     => $totalHrs,
                'certifications'  => $certs->count(),
                'valid_certs'     => $validCerts->count(),
                'expired_certs'   => $expiredCerts->count(),
            ];
        });

        return view('training.report', compact(
            'report', 'departments', 'totalHours', 'totalCost'
        ));
    }

    private function authorizeSession(TrainingSession $session): void {
        if ($session->company_id !== auth()->user()->company_id) abort(403);
    }
}