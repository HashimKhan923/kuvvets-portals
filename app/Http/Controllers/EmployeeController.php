<?php
namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\User;
use App\Services\EmailService;
use App\Models\Department;
use App\Models\Designation;
use App\Models\EmployeeDocument;
use App\Models\EmployeeNote;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class EmployeeController extends Controller
{
    // ── LIST ─────────────────────────────────────────────────
    public function index(Request $request)
    {
        $query = Employee::with(['department', 'designation', 'manager'])
            ->where('company_id', auth()->user()->company_id);

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('first_name', 'like', "%$s%")
                  ->orWhere('last_name',  'like', "%$s%")
                  ->orWhere('employee_id','like', "%$s%")
                  ->orWhere('cnic',       'like', "%$s%")
                  ->orWhere('work_email', 'like', "%$s%");
            });
        }

        if ($request->filled('department'))
            $query->where('department_id', $request->department);

        if ($request->filled('status'))
            $query->where('employment_status', $request->status);

        if ($request->filled('type'))
            $query->where('employment_type', $request->type);

        $employees   = $query->latest()->paginate(15)->withQueryString();
        $departments = Department::where('company_id', auth()->user()->company_id)
                        ->where('is_active', true)->get();

        $stats = [
            'total'       => Employee::where('company_id', auth()->user()->company_id)->where('employment_status','active')->count(),
            'probation'   => Employee::where('company_id', auth()->user()->company_id)->where('probation_status','on_probation')->count(),
            'contract'    => Employee::where('company_id', auth()->user()->company_id)->where('employment_type','contract')->count(),
            'new_month'   => Employee::where('company_id', auth()->user()->company_id)
                                ->whereMonth('joining_date', now()->month)
                                ->whereYear('joining_date', now()->year)->count(),
        ];

        return view('employees.index', compact('employees', 'departments', 'stats'));
    }

    // ── CREATE FORM ──────────────────────────────────────────
    public function create()
    {
        $departments  = Department::where('company_id', auth()->user()->company_id)->where('is_active', true)->get();
        $designations = Designation::where('company_id', auth()->user()->company_id)->where('is_active', true)->get();
        $managers     = Employee::where('company_id', auth()->user()->company_id)
                            ->where('employment_status', 'active')
                            ->orderBy('first_name')->get();
        $nextId       = $this->generateEmployeeId();

        return view('employees.create', compact('departments', 'designations', 'managers', 'nextId'));
    }

    // ── STORE ─────────────────────────────────────────────────
    public function store(Request $request)
    {
        $validated = $request->validate($this->validationRules(), $this->validationMessages());

        DB::transaction(function () use ($request, $validated) {
            // Handle avatar upload
            if ($request->hasFile('avatar')) {
                $validated['avatar'] = $request->file('avatar')
                    ->store('avatars', 'public');
            }

            $validated['company_id'] = auth()->user()->company_id;
            $employee = Employee::create($validated);

            // Create portal user account if requested
            if ($request->boolean('create_user_account')) {
                $user = User::create([
                    'company_id'         => auth()->user()->company_id,
                    'name'               => $employee->full_name,
                    'email'              => $validated['work_email'],
                    'username'           => strtolower($employee->employee_id),
                    'password'           => Hash::make('Kuvvet@' . now()->year . '!'),
                    'email_verified_at'  => now(),
                    'is_active'          => true,
                    'password_changed_at'=> now(),
                ]);
                $user->assignRole('employee');
                $employee->update(['user_id' => $user->id]);
                $plainPassword = 'Kuvvet@' . now()->year . '!';
                EmailService::welcomeEmployee($employee, strtolower($employee->employee_id), $plainPassword);
            }

            AuditLog::log('employee_created', $employee, [], $employee->toArray());
        });

        return redirect()->route('employees.index')
            ->with('success', 'Employee created successfully.');
    }

    // ── SHOW / PROFILE ───────────────────────────────────────
    public function show(Employee $employee)
    {
        $this->authorizeCompany($employee);
        $employee->load([
            'department', 'designation', 'manager',
            'documents', 'notes.author',
        ]);
        return view('employees.show', compact('employee'));
    }

    // ── EDIT ─────────────────────────────────────────────────
    public function edit(Employee $employee)
    {
        $this->authorizeCompany($employee);
        $departments  = Department::where('company_id', auth()->user()->company_id)->where('is_active', true)->get();
        $designations = Designation::where('company_id', auth()->user()->company_id)->where('is_active', true)->get();
        $managers     = Employee::where('company_id', auth()->user()->company_id)
                            ->where('employment_status', 'active')
                            ->where('id', '!=', $employee->id)
                            ->orderBy('first_name')->get();
        return view('employees.edit', compact('employee', 'departments', 'designations', 'managers'));
    }

    // ── UPDATE ───────────────────────────────────────────────
    public function update(Request $request, Employee $employee)
    {
        $this->authorizeCompany($employee);

        $rules = $this->validationRules($employee->id);
        $validated = $request->validate($rules, $this->validationMessages());

        $old = $employee->toArray();

        if ($request->hasFile('avatar')) {
            if ($employee->avatar) Storage::disk('public')->delete($employee->avatar);
            $validated['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $employee->update($validated);
        AuditLog::log('employee_updated', $employee, $old, $validated);

        return redirect()->route('employees.show', $employee)
            ->with('success', 'Employee profile updated successfully.');
    }

    // ── DESTROY ──────────────────────────────────────────────
    public function destroy(Employee $employee)
    {
        $this->authorizeCompany($employee);
        AuditLog::log('employee_deleted', $employee, $employee->toArray(), []);
        $employee->delete();
        return redirect()->route('employees.index')
            ->with('success', 'Employee removed from system.');
    }

    // ── UPLOAD DOCUMENT ──────────────────────────────────────
    public function uploadDocument(Request $request, Employee $employee)
    {
        $this->authorizeCompany($employee);

        $request->validate([
            'title'       => 'required|string|max:255',
            'type'        => 'required|string',
            'document'    => 'required|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240',
            'issue_date'  => 'nullable|date',
            'expiry_date' => 'nullable|date|after:issue_date',
        ]);

        $file = $request->file('document');
        $path = $file->store("documents/{$employee->id}", 'public');

        EmployeeDocument::create([
            'employee_id' => $employee->id,
            'title'       => $request->title,
            'type'        => $request->type,
            'file_path'   => $path,
            'file_name'   => $file->getClientOriginalName(),
            'file_size'   => round($file->getSize() / 1024) . ' KB',
            'issue_date'  => $request->issue_date,
            'expiry_date' => $request->expiry_date,
            'uploaded_by' => auth()->id(),
        ]);

        return back()->with('success', 'Document uploaded successfully.');
    }

    // ── ADD NOTE ─────────────────────────────────────────────
    public function addNote(Request $request, Employee $employee)
    {
        $this->authorizeCompany($employee);

        $request->validate([
            'title' => 'nullable|string|max:255',
            'body'  => 'required|string',
            'type'  => 'required|string',
        ]);

        EmployeeNote::create([
            'employee_id' => $employee->id,
            'created_by'  => auth()->id(),
            'title'       => $request->title,
            'body'        => $request->body,
            'type'        => $request->type,
            'is_private'  => $request->boolean('is_private'),
        ]);

        return back()->with('success', 'Note added successfully.');
    }

    // ── HELPERS ──────────────────────────────────────────────
    private function generateEmployeeId(): string
    {
        $last = Employee::where('company_id', auth()->user()->company_id)
                    ->orderBy('id', 'desc')->first();
        $num  = $last ? (intval(substr($last->employee_id, -4)) + 1) : 1;
        return 'KVT-' . str_pad($num, 4, '0', STR_PAD_LEFT);
    }

    private function authorizeCompany(Employee $employee): void
    {
        if ($employee->company_id !== auth()->user()->company_id) {
            abort(403, 'Unauthorized');
        }
    }

    private function validationRules(?int $ignoreId = null): array
    {
        return [
            'employee_id'    => ['required', 'string', Rule::unique('employees')->ignore($ignoreId)],
            'first_name'     => 'required|string|max:100',
            'last_name'      => 'required|string|max:100',
            'father_name'    => 'nullable|string|max:100',
            'cnic'           => ['nullable','string','regex:/^\d{5}-\d{7}-\d{1}$/', Rule::unique('employees')->ignore($ignoreId)],
            'date_of_birth'  => 'nullable|date|before:-18 years',
            'gender'         => 'nullable|in:male,female,other',
            'marital_status' => 'nullable|in:single,married,divorced,widowed',
            'nationality'    => 'nullable|string',
            'personal_email' => 'nullable|email',
            'work_email'     => ['nullable','email', Rule::unique('employees')->ignore($ignoreId)],
            'personal_phone' => 'nullable|string|max:20',
            'work_phone'     => 'nullable|string|max:20',
            'whatsapp'       => 'nullable|string|max:20',
            'current_address'=> 'nullable|string',
            'current_city'   => 'nullable|string|max:100',
            'province'       => 'nullable|string|max:100',
            'department_id'  => 'nullable|exists:departments,id',
            'designation_id' => 'nullable|exists:designations,id',
            'manager_id'     => 'nullable|exists:employees,id',
            'joining_date'   => 'nullable|date',
            'employment_type'=> 'required|in:permanent,contract,probationary,part_time,internship,daily_wages',
            'basic_salary'   => 'nullable|numeric|min:0',
            'bank_name'      => 'nullable|string|max:100',
            'bank_account_no'=> 'nullable|string|max:50',
            'bank_iban'      => 'nullable|string|max:34',
            'eobi_number'    => 'nullable|string|max:50',
            'avatar'         => 'nullable|image|max:2048',
        ];
    }

    private function validationMessages(): array
    {
        return [
            'cnic.regex'          => 'CNIC must be in format: 42201-1234567-1',
            'date_of_birth.before'=> 'Employee must be at least 18 years old.',
            'work_email.unique'   => 'This work email is already assigned to another employee.',
        ];
    }
}