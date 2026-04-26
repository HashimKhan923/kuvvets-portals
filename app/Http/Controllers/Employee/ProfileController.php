<?php
namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\EmployeeDocument;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use App\Services\EmailService;
class ProfileController extends Controller
{
    /**
     * Main profile page.
     */
    public function index(Request $request)
    {
        $employee = $request->user()->employee->load(['department','designation','manager','company']);

        $tab = $request->input('tab', 'overview');
        $allowedTabs = ['overview','edit','password','documents','employment'];
        if (!in_array($tab, $allowedTabs)) $tab = 'overview';

        $completion = $this->profileCompletion($employee);

        $documents = EmployeeDocument::where('employee_id', $employee->id)
            ->orderByDesc('created_at')
            ->get();

        return view('employee.profile.index', compact('employee','tab','completion','documents'));
    }

    /**
     * Update editable personal info.
     */
    public function update(Request $request)
    {
        $employee = $request->user()->employee;

        $data = $request->validate([
            'personal_email'           => ['nullable','email','max:255', Rule::unique('employees','personal_email')->ignore($employee->id)],
            'personal_phone'           => 'nullable|string|max:20',
            'whatsapp'                 => 'nullable|string|max:20',
            'current_address'          => 'nullable|string|max:500',
            'current_city'             => 'nullable|string|max:100',
            'permanent_address'        => 'nullable|string|max:500',
            'permanent_city'           => 'nullable|string|max:100',
            'province'                 => 'nullable|string|max:100',
            'marital_status'           => 'nullable|in:single,married,divorced,widowed',
            'religion'                 => 'nullable|string|max:50',
            // Emergency contact
            'emergency_contact_name'     => 'nullable|string|max:100',
            'emergency_contact_relation' => 'nullable|string|max:50',
            'emergency_contact_phone'    => 'nullable|string|max:20',
            // Bank (employee-editable)
            'bank_name'       => 'nullable|string|max:100',
            'bank_account_no' => 'nullable|string|max:50',
            'bank_iban'       => 'nullable|string|max:34',
            'bank_branch'     => 'nullable|string|max:150',
        ]);

        $before = $employee->only(array_keys($data));
        $employee->update($data);

        AuditLog::log('profile_updated', $employee, $before, $data);

        return redirect()->route('employee.profile.index', ['tab'=>'edit'])
            ->with('success', 'Profile updated successfully.');
    }

    /**
     * Update avatar.
     */
    public function updateAvatar(Request $request)
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $employee = $request->user()->employee;

        // Delete old avatar
        if ($employee->avatar && Storage::disk('public')->exists($employee->avatar)) {
            Storage::disk('public')->delete($employee->avatar);
        }

        $path = $request->file('avatar')->store('avatars/'.$employee->id, 'public');
        $employee->update(['avatar' => $path]);

        AuditLog::log('avatar_updated', $employee);

        return back()->with('success', 'Profile photo updated.');
    }

    /**
     * Remove avatar.
     */
    public function removeAvatar(Request $request)
    {
        $employee = $request->user()->employee;
        if ($employee->avatar && Storage::disk('public')->exists($employee->avatar)) {
            Storage::disk('public')->delete($employee->avatar);
        }
        $employee->update(['avatar' => null]);

        return back()->with('success', 'Profile photo removed.');
    }

    /**
     * Change password.
     */
    public function updatePassword(Request $request)
    {
        $data = $request->validate([
            'current_password' => 'required',
            'password'         => ['required','confirmed', Password::min(8)->mixedCase()->numbers()],
        ], [
            'password.confirmed' => 'New password and confirmation do not match.',
        ]);

        $user = $request->user();

        if (!Hash::check($data['current_password'], $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        if (Hash::check($data['password'], $user->password)) {
            return back()->withErrors(['password' => 'New password must be different from the current one.']);
        }

        $user->update([
            'password'         => Hash::make($data['password']),
            'password_changed_at' => now(),
        ]);

        AuditLog::log('password_changed');
        EmailService::passwordChanged($user);

        return redirect()->route('employee.profile.index', ['tab'=>'password'])
            ->with('success', 'Password changed successfully.');
    }

    /**
     * Upload a personal document.
     */
    public function uploadDocument(Request $request)
    {
        $data = $request->validate([
            'title'       => 'required|string|max:150',
            'type'        => 'required|in:cnic,passport,contract,offer_letter,experience_letter,degree,certificate,noc,other',
            'file'        => 'required|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240',
            'issue_date'  => 'nullable|date',
            'expiry_date' => 'nullable|date|after_or_equal:issue_date',
            'notes'       => 'nullable|string|max:500',
        ]);

        $employee = $request->user()->employee;
        $file = $request->file('file');

        $path = $file->store('employee_documents/'.$employee->id, 'public');

        EmployeeDocument::create([
            'employee_id'   => $employee->id,
            'title'         => $data['title'],
            'type'          => $data['type'],
            'file_path'     => $path,
            'file_name'     => $file->getClientOriginalName(),
            'file_size'     => $this->formatBytes($file->getSize()),
            'issue_date'    => $data['issue_date'] ?? null,
            'expiry_date'   => $data['expiry_date'] ?? null,
            'notes'         => $data['notes'] ?? null,
            'uploaded_by'   => $request->user()->id,
            'is_verified'   => false,
        ]);

        AuditLog::log('document_uploaded', $employee, [], ['title'=>$data['title'],'type'=>$data['type']]);

        return redirect()->route('employee.profile.index', ['tab'=>'documents'])
            ->with('success', 'Document uploaded successfully. HR will review and verify it.');
    }

    /**
     * Download a document.
     */
    public function downloadDocument(Request $request, EmployeeDocument $document)
    {
        abort_unless($document->employee_id === $request->user()->employee->id, 403);

        if (!Storage::disk('public')->exists($document->file_path)) {
            abort(404, 'File not found');
        }

        return response()->download(
            Storage::disk('public')->path($document->file_path),
            $document->file_name
        );
    }

    /**
     * Delete own document (only if not verified by HR).
     */
    public function deleteDocument(Request $request, EmployeeDocument $document)
    {
        abort_unless($document->employee_id === $request->user()->employee->id, 403);

        if ($document->is_verified) {
            return back()->with('error', 'Cannot delete a verified document. Contact HR.');
        }

        if (Storage::disk('public')->exists($document->file_path)) {
            Storage::disk('public')->delete($document->file_path);
        }

        AuditLog::log('document_deleted', $request->user()->employee, ['title'=>$document->title], []);
        $document->delete();

        return back()->with('success', 'Document deleted.');
    }

    // ══════════════════════════════════════════════════════════════════
    // HELPERS
    // ══════════════════════════════════════════════════════════════════

    /**
     * Calculate profile completion %.
     */
    protected function profileCompletion($employee): array
    {
        $sections = [
            'Personal Information' => [
                'personal_email'        => $employee->personal_email,
                'personal_phone'        => $employee->personal_phone,
                'date_of_birth'         => $employee->date_of_birth,
                'gender'                => $employee->gender,
                'marital_status'        => $employee->marital_status,
                'father_name'           => $employee->father_name,
            ],
            'Address' => [
                'current_address' => $employee->current_address,
                'current_city'    => $employee->current_city,
                'province'        => $employee->province,
            ],
            'Emergency Contact' => [
                'emergency_contact_name'     => $employee->emergency_contact_name,
                'emergency_contact_phone'    => $employee->emergency_contact_phone,
                'emergency_contact_relation' => $employee->emergency_contact_relation,
            ],
            'Bank Information' => [
                'bank_name'       => $employee->bank_name,
                'bank_account_no' => $employee->bank_account_no,
                'bank_iban'       => $employee->bank_iban,
            ],
            'Identification' => [
                'cnic'   => $employee->cnic,
                'avatar' => $employee->avatar,
            ],
        ];

        $results = [];
        $totalFilled = 0;
        $totalFields = 0;

        foreach ($sections as $name => $fields) {
            $filled = count(array_filter($fields, fn($v) => !empty($v)));
            $count  = count($fields);
            $results[$name] = [
                'filled' => $filled,
                'total'  => $count,
                'pct'    => $count ? round(($filled / $count) * 100) : 0,
            ];
            $totalFilled += $filled;
            $totalFields += $count;
        }

        return [
            'overall_pct' => $totalFields ? round(($totalFilled / $totalFields) * 100) : 0,
            'sections'    => $results,
        ];
    }

    protected function formatBytes(int $bytes): string
    {
        if ($bytes < 1024) return "{$bytes} B";
        if ($bytes < 1024 * 1024) return round($bytes / 1024, 1) . ' KB';
        if ($bytes < 1024 * 1024 * 1024) return round($bytes / (1024 * 1024), 1) . ' MB';
        return round($bytes / (1024 * 1024 * 1024), 2) . ' GB';
    }
}