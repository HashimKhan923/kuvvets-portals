<?php
namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DepartmentController extends Controller
{
    public function index()
    {
        $departments = Department::with(['parent', 'children', 'employees' => function($q) {
                $q->where('employment_status', 'active');
            }])
            ->where('company_id', auth()->user()->company_id)
            ->whereNull('parent_id')
            ->withCount(['employees' => function($q) {
                $q->where('employment_status', 'active');
            }])
            ->latest()
            ->get();

        $allDepartments = Department::where('company_id', auth()->user()->company_id)
            ->where('is_active', true)
            ->get();

        $stats = [
            'total'       => Department::where('company_id', auth()->user()->company_id)->count(),
            'active'      => Department::where('company_id', auth()->user()->company_id)->where('is_active', true)->count(),
            'total_staff' => Employee::where('company_id', auth()->user()->company_id)->where('employment_status', 'active')->count(),
            'designations'=> Designation::where('company_id', auth()->user()->company_id)->count(),
        ];

        return view('departments.index', compact('departments', 'allDepartments', 'stats'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'        => ['required','string','max:100',
                              Rule::unique('departments')->where('company_id', auth()->user()->company_id)],
            'code'        => 'nullable|string|max:20',
            'parent_id'   => 'nullable|exists:departments,id',
            'description' => 'nullable|string|max:500',
            'cost_center' => 'nullable|string|max:50',
        ], [
            'name.unique' => 'A department with this name already exists.',
        ]);

        $dept = Department::create([
            'company_id'  => auth()->user()->company_id,
            'name'        => $request->name,
            'code'        => $request->code ?? strtoupper(substr($request->name, 0, 4)) . rand(10, 99),
            'parent_id'   => $request->parent_id,
            'description' => $request->description,
            'cost_center' => $request->cost_center,
            'is_active'   => true,
        ]);

        AuditLog::log('department_created', $dept);
        return back()->with('success', "Department \"{$dept->name}\" created successfully.");
    }

    public function show(Department $department)
    {
        $this->authorizeCompany($department);

        $department->load([
            'parent',
            'children.employees' => fn($q) => $q->where('employment_status', 'active'),
            'designations',
            'employees' => fn($q) => $q->where('employment_status', 'active')
                                       ->with(['designation', 'manager']),
        ]);

        $department->loadCount([
            'employees' => fn($q) => $q->where('employment_status', 'active'),
        ]);

        return view('departments.show', compact('department'));
    }

    public function update(Request $request, Department $department)
    {
        $this->authorizeCompany($department);

        $request->validate([
            'name'        => ['required','string','max:100',
                              Rule::unique('departments')
                                  ->where('company_id', auth()->user()->company_id)
                                  ->ignore($department->id)],
            'code'        => 'nullable|string|max:20',
            'parent_id'   => ['nullable','exists:departments,id',
                              Rule::notIn([$department->id])],
            'description' => 'nullable|string|max:500',
            'cost_center' => 'nullable|string|max:50',
            'is_active'   => 'boolean',
        ]);

        $old = $department->toArray();
        $department->update($request->only([
            'name','code','parent_id','description','cost_center','is_active'
        ]));

        AuditLog::log('department_updated', $department, $old, $department->toArray());
        return back()->with('success', "Department updated successfully.");
    }

    public function destroy(Department $department)
    {
        $this->authorizeCompany($department);

        if ($department->employees()->where('employment_status', 'active')->exists()) {
            return back()->with('error', 'Cannot delete department with active employees. Reassign them first.');
        }

        if ($department->children()->exists()) {
            return back()->with('error', 'Cannot delete department that has sub-departments.');
        }

        AuditLog::log('department_deleted', $department, $department->toArray(), []);
        $department->delete();

        return back()->with('success', "Department \"{$department->name}\" deleted.");
    }

    public function toggleStatus(Department $department)
    {
        $this->authorizeCompany($department);
        $department->update(['is_active' => !$department->is_active]);
        $status = $department->is_active ? 'activated' : 'deactivated';
        return back()->with('success', "Department {$status}.");
    }

    // ── Designation sub-resource ─────────────────────────────
    public function storeDesignation(Request $request, Department $department)
    {
        $this->authorizeCompany($department);

        $request->validate([
            'title'      => 'required|string|max:100',
            'grade'      => 'nullable|string|max:50',
            'level'      => 'required|in:junior,mid,senior,lead,manager,director,c_level',
            'min_salary' => 'nullable|numeric|min:0',
            'max_salary' => 'nullable|numeric|min:0|gte:min_salary',
        ]);

        Designation::create([
            'company_id'     => auth()->user()->company_id,
            'department_id'  => $department->id,
            'title'          => $request->title,
            'grade'          => $request->grade,
            'level'          => $request->level,
            'min_salary'     => $request->min_salary,
            'max_salary'     => $request->max_salary,
            'is_active'      => true,
        ]);

        return back()->with('success', "Designation \"{$request->title}\" added.");
    }

    public function destroyDesignation(Designation $designation)
    {
        if ($designation->employees()->exists()) {
            return back()->with('error', 'Cannot delete designation assigned to employees.');
        }
        $designation->delete();
        return back()->with('success', 'Designation removed.');
    }

    private function authorizeCompany(Department $department): void
    {
        if ($department->company_id !== auth()->user()->company_id) abort(403);
    }
}