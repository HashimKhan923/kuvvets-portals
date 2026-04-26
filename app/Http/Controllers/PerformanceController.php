<?php
namespace App\Http\Controllers;

use App\Models\PerformanceCycle;
use App\Models\Kpi;
use App\Models\EmployeeGoal;
use App\Models\Appraisal;
use App\Models\Feedback360;
use App\Models\Employee;
use App\Models\Department;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Carbon\Carbon;

class PerformanceController extends Controller
{
    // ── DASHBOARD ────────────────────────────────────────────
    public function index()
    {
        $companyId = auth()->user()->company_id;

        $stats = [
            'active_cycles'   => PerformanceCycle::where('company_id', $companyId)
                ->where('status', 'active')->count(),
            'total_appraisals'=> Appraisal::where('company_id', $companyId)
                ->whereHas('cycle', fn($q) => $q->where('status', 'active'))->count(),
            'pending_reviews' => Appraisal::where('company_id', $companyId)
                ->whereIn('status', ['pending', 'self_review', 'manager_review'])->count(),
            'completed'       => Appraisal::where('company_id', $companyId)
                ->where('status', 'completed')->count(),
            'goals_on_track'  => EmployeeGoal::whereHas('employee',
                    fn($q) => $q->where('company_id', $companyId))
                ->where('status', 'in_progress')
                ->where('progress', '>=', 50)->count(),
            'goals_at_risk'   => EmployeeGoal::whereHas('employee',
                    fn($q) => $q->where('company_id', $companyId))
                ->where('status', 'in_progress')
                ->where('progress', '<', 30)->count(),
        ];

        $cycles = PerformanceCycle::where('company_id', $companyId)
            ->withCount('appraisals')
            ->latest()->take(5)->get();

        $recentAppraisals = Appraisal::with(['employee.department', 'cycle'])
            ->where('company_id', $companyId)
            ->latest()->take(8)->get();

        // Rating distribution
        $ratingDist = Appraisal::where('company_id', $companyId)
            ->where('status', 'completed')
            ->whereNotNull('overall_rating')
            ->selectRaw('overall_rating, COUNT(*) as count')
            ->groupBy('overall_rating')
            ->pluck('count', 'overall_rating');

        // Department avg scores
        $deptScores = Appraisal::where('company_id', $companyId)
            ->where('status', 'completed')
            ->whereNotNull('overall_score')
            ->with('employee.department')
            ->get()
            ->groupBy(fn($a) => $a->employee->department?->name ?? 'Unassigned')
            ->map(fn($group) => round($group->avg('overall_score'), 2));

        return view('performance.index', compact(
            'stats', 'cycles', 'recentAppraisals', 'ratingDist', 'deptScores'
        ));
    }

    // ── CYCLES LIST ──────────────────────────────────────────
    public function cycles()
    {
        $cycles = PerformanceCycle::where('company_id', auth()->user()->company_id)
            ->withCount(['appraisals', 'goals'])
            ->latest()->get();

        return view('performance.cycles', compact('cycles'));
    }

    // ── CREATE CYCLE ─────────────────────────────────────────
    public function storeCycle(Request $request)
    {
        $request->validate([
            'name'              => 'required|string|max:150',
            'type'              => 'required|string',
            'start_date'        => 'required|date',
            'end_date'          => 'required|date|after:start_date',
            'review_start_date' => 'nullable|date',
            'review_end_date'   => 'nullable|date|after:review_start_date',
            'description'       => 'nullable|string',
        ]);

        $cycle = PerformanceCycle::create([
            'company_id'        => auth()->user()->company_id,
            'created_by'        => auth()->id(),
            'name'              => $request->name,
            'type'              => $request->type,
            'start_date'        => $request->start_date,
            'end_date'          => $request->end_date,
            'review_start_date' => $request->review_start_date,
            'review_end_date'   => $request->review_end_date,
            'description'       => $request->description,
            'status'            => 'draft',
        ]);

        AuditLog::log('performance_cycle_created', $cycle);
        return redirect()->route('performance.cycle', $cycle)
            ->with('success', "Performance cycle \"{$cycle->name}\" created.");
    }

    // ── UPDATE CYCLE STATUS ──────────────────────────────────
    public function updateCycleStatus(Request $request, PerformanceCycle $cycle)
    {
        $this->authorizeCycle($cycle);
        $request->validate(['status' => 'required|in:draft,active,review,completed,cancelled']);
        $cycle->update(['status' => $request->status]);
        return back()->with('success', 'Cycle status updated to ' . ucfirst($request->status) . '.');
    }

    // ── CYCLE DETAIL ─────────────────────────────────────────
    public function showCycle(PerformanceCycle $cycle)
    {
        $this->authorizeCycle($cycle);
        $cycle->load(['creator']);

        $appraisals = Appraisal::with(['employee.department', 'employee.designation'])
            ->where('performance_cycle_id', $cycle->id)
            ->get();

        $goals = EmployeeGoal::with(['employee', 'kpi'])
            ->where('performance_cycle_id', $cycle->id)
            ->get();

        $employees   = Employee::where('company_id', auth()->user()->company_id)
            ->where('employment_status', 'active')
            ->with(['department', 'designation'])->get();

        $departments = Department::where('company_id', auth()->user()->company_id)->get();

        return view('performance.cycle', compact(
            'cycle', 'appraisals', 'goals', 'employees', 'departments'
        ));
    }

    // ── GENERATE APPRAISALS ──────────────────────────────────
    public function generateAppraisals(Request $request, PerformanceCycle $cycle)
    {
        $this->authorizeCycle($cycle);

        $companyId = auth()->user()->company_id;
        $employees = Employee::where('company_id', $companyId)
            ->where('employment_status', 'active')->get();

        $created = 0;
        foreach ($employees as $emp) {
            if (Appraisal::where('performance_cycle_id', $cycle->id)
                ->where('employee_id', $emp->id)->exists()) continue;

            Appraisal::create([
                'performance_cycle_id' => $cycle->id,
                'employee_id'          => $emp->id,
                'appraiser_id'         => auth()->id(),
                'company_id'           => $companyId,
                'appraisal_number'     => 'APR-' . $cycle->id . '-' . str_pad($emp->id, 4, '0', STR_PAD_LEFT),
                'type'                 => 'manager',
                'status'               => 'pending',
            ]);
            $created++;
        }

        return back()->with('success', "{$created} appraisals generated for {$cycle->name}.");
    }

    // ── SHOW APPRAISAL ───────────────────────────────────────
    public function showAppraisal(Appraisal $appraisal)
    {
        $this->authorizeAppraisal($appraisal);
        $appraisal->load(['employee.department', 'employee.designation',
                          'cycle', 'appraiser', 'feedback.reviewer']);

        $goals = EmployeeGoal::where('employee_id', $appraisal->employee_id)
            ->where('performance_cycle_id', $appraisal->performance_cycle_id)
            ->get();

        return view('performance.appraisal', compact('appraisal', 'goals'));
    }

    // ── SUBMIT APPRAISAL SCORES ──────────────────────────────
    public function submitAppraisal(Request $request, Appraisal $appraisal)
    {
        $this->authorizeAppraisal($appraisal);

        $request->validate([
            'job_knowledge_score'    => 'required|numeric|min:1|max:5',
            'work_quality_score'     => 'required|numeric|min:1|max:5',
            'productivity_score'     => 'required|numeric|min:1|max:5',
            'communication_score'    => 'required|numeric|min:1|max:5',
            'teamwork_score'         => 'required|numeric|min:1|max:5',
            'initiative_score'       => 'required|numeric|min:1|max:5',
            'attendance_score'       => 'required|numeric|min:1|max:5',
            'leadership_score'       => 'required|numeric|min:1|max:5',
            'goal_achievement_score' => 'required|numeric|min:1|max:5',
            'strengths'              => 'nullable|string',
            'improvements'           => 'nullable|string',
            'achievements'           => 'nullable|string',
            'training_needs'         => 'nullable|string',
            'manager_comments'       => 'nullable|string',
            'increment_recommended'  => 'nullable|numeric|min:0|max:100',
            'promotion_recommended'  => 'nullable|boolean',
        ]);

        $appraisal->fill($request->only([
            'job_knowledge_score', 'work_quality_score', 'productivity_score',
            'communication_score', 'teamwork_score', 'initiative_score',
            'attendance_score', 'leadership_score', 'goal_achievement_score',
            'strengths', 'improvements', 'achievements', 'training_needs',
            'manager_comments', 'increment_recommended',
        ]));

        $appraisal->promotion_recommended = $request->boolean('promotion_recommended');
        $appraisal->overall_score         = $appraisal->calculateOverallScore();
        $appraisal->overall_rating        = $appraisal->determineRating();
        $appraisal->status                = 'completed';
        $appraisal->submitted_at          = now();
        $appraisal->completed_at          = now();
        $appraisal->appraiser_id          = auth()->id();
        $appraisal->save();

        AuditLog::log('appraisal_submitted', $appraisal);
        return back()->with('success', 'Appraisal submitted successfully.');
    }

    // ── ASSIGN GOAL ──────────────────────────────────────────
    public function assignGoal(Request $request, PerformanceCycle $cycle)
    {
        $this->authorizeCycle($cycle);

        $request->validate([
            'employee_id'  => 'required|exists:employees,id',
            'title'        => 'required|string|max:200',
            'category'     => 'required|string',
            'target_value' => 'nullable|numeric',
            'unit'         => 'nullable|string|max:50',
            'weight'       => 'nullable|integer|min:1|max:100',
            'due_date'     => 'nullable|date',
            'description'  => 'nullable|string',
        ]);

        EmployeeGoal::create([
            'employee_id'          => $request->employee_id,
            'performance_cycle_id' => $cycle->id,
            'kpi_id'               => $request->kpi_id,
            'assigned_by'          => auth()->id(),
            'title'                => $request->title,
            'description'          => $request->description,
            'category'             => $request->category,
            'target_value'         => $request->target_value,
            'unit'                 => $request->unit,
            'weight'               => $request->weight ?? 10,
            'due_date'             => $request->due_date,
            'status'               => 'not_started',
        ]);

        return back()->with('success', 'Goal assigned successfully.');
    }

    // ── UPDATE GOAL PROGRESS ─────────────────────────────────
    public function updateGoal(Request $request, EmployeeGoal $goal)
    {
        $request->validate([
            'progress'          => 'required|integer|min:0|max:100',
            'achieved_value'    => 'nullable|numeric',
            'status'            => 'required|string',
            'manager_comments'  => 'nullable|string',
        ]);

        $goal->update([
            'progress'         => $request->progress,
            'achieved_value'   => $request->achieved_value,
            'status'           => $request->status,
            'manager_comments' => $request->manager_comments,
        ]);

        return back()->with('success', 'Goal progress updated.');
    }

    // ── KPI MANAGEMENT ───────────────────────────────────────
    public function kpis(Request $request)
    {
        $companyId   = auth()->user()->company_id;
        $departments = Department::where('company_id', $companyId)->get();

        $query = Kpi::where('company_id', $companyId)->withCount('goals');
        if ($request->filled('department'))
            $query->where('department_id', $request->department);
        if ($request->filled('category'))
            $query->where('category', $request->category);

        $kpis = $query->get();

        return view('performance.kpis', compact('kpis', 'departments'));
    }

    public function storeKpi(Request $request)
    {
        $request->validate([
            'title'            => 'required|string|max:150',
            'category'         => 'required|string',
            'measurement_type' => 'required|string',
            'target_value'     => 'nullable|numeric',
            'weight'           => 'nullable|integer|min:1|max:100',
            'unit'             => 'nullable|string|max:50',
        ]);

        Kpi::create([
            'company_id'       => auth()->user()->company_id,
            'department_id'    => $request->department_id,
            'title'            => $request->title,
            'description'      => $request->description,
            'category'         => $request->category,
            'measurement_type' => $request->measurement_type,
            'unit'             => $request->unit,
            'target_value'     => $request->target_value,
            'weight'           => $request->weight ?? 10,
            'is_active'        => true,
        ]);

        return back()->with('success', "KPI \"{$request->title}\" created.");
    }

    // ── REPORT ───────────────────────────────────────────────
    public function report(Request $request)
    {
        $companyId   = auth()->user()->company_id;
        $departments = Department::where('company_id', $companyId)->get();
        $cycles      = PerformanceCycle::where('company_id', $companyId)
            ->orderByDesc('start_date')->get();

        $cycleId = $request->filled('cycle')
            ? $request->cycle
            : $cycles->first()?->id;

        $appraisals = collect();
        $cycle      = null;

        if ($cycleId) {
            $cycle = PerformanceCycle::find($cycleId);
            $query = Appraisal::with(['employee.department', 'employee.designation'])
                ->where('performance_cycle_id', $cycleId)
                ->where('company_id', $companyId)
                ->where('status', 'completed');

            if ($request->filled('department'))
                $query->whereHas('employee',
                    fn($q) => $q->where('department_id', $request->department));

            $appraisals = $query->orderByDesc('overall_score')->get();
        }

        return view('performance.report', compact(
            'appraisals', 'cycles', 'cycle', 'departments'
        ));
    }

    // ── HELPERS ──────────────────────────────────────────────
    private function authorizeCycle(PerformanceCycle $cycle): void {
        if ($cycle->company_id !== auth()->user()->company_id) abort(403);
    }

    private function authorizeAppraisal(Appraisal $appraisal): void {
        if ($appraisal->company_id !== auth()->user()->company_id) abort(403);
    }
}