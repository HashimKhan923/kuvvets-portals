<?php
namespace App\Services;

use App\Mail\AttendanceOverridden;
use App\Mail\DocumentExpiryReminder;
use App\Mail\LeaveCancelled;
use App\Mail\LeaveApproved;
use App\Mail\LeaveRejected;
use App\Mail\LeaveSubmitted;
use App\Mail\PasswordChanged;
use App\Mail\PayslipPublished;
use App\Mail\PortalAccessGranted;
use App\Mail\WelcomeEmployee;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\EmployeeDocument;
use App\Models\LeaveRequest;
use App\Models\Payslip;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EmailService
{
    /**
     * Safely send and log any email — never crash the main request.
     */
    protected static function send(string $to, string $toName, object $mailable): void
    {
        try {
            Mail::to($to, $toName)->queue($mailable);
        } catch (\Throwable $e) {
            Log::error('EmailService failed', [
                'mailable' => get_class($mailable),
                'to'       => $to,
                'error'    => $e->getMessage(),
            ]);
        }
    }

    /**
     * Helper — get employee email (work or personal, whichever is set).
     */
    protected static function employeeEmail(Employee $employee): ?string
    {
        return $employee->work_email ?: $employee->personal_email ?: null;
    }

    // ═══════════════════════════════════════════════════════════
    // PUBLIC API
    // ═══════════════════════════════════════════════════════════

    public static function welcomeEmployee(Employee $employee, string $username, string $plainPassword): void
    {
        $email = static::employeeEmail($employee);
        if (!$email) return;

        static::send($email, $employee->full_name, new WelcomeEmployee($employee, $username, $plainPassword));
    }

    public static function portalAccessGranted(Employee $employee, string $username, string $plainPassword): void
    {
        $email = static::employeeEmail($employee);
        if (!$email) return;

        static::send($email, $employee->full_name, new PortalAccessGranted($employee, $username, $plainPassword));
    }

    public static function leaveSubmitted(LeaveRequest $leaveRequest): void
    {
        $leaveRequest->load(['employee','leaveType']);

        // 1. Notify employee
        $email = static::employeeEmail($leaveRequest->employee);
        if ($email) {
            static::send($email, $leaveRequest->employee->full_name, new LeaveSubmitted($leaveRequest));
        }

        // 2. Notify HR managers (users with leaves.approve permission in same company)
        static::notifyHrManagers(
            $leaveRequest->employee->company_id,
            new LeaveSubmitted($leaveRequest),
            "New leave request from {$leaveRequest->employee->full_name}"
        );
    }

    public static function leaveApproved(LeaveRequest $leaveRequest): void
    {
        $leaveRequest->load(['employee','leaveType','reviewer']);
        $email = static::employeeEmail($leaveRequest->employee);
        if (!$email) return;

        static::send($email, $leaveRequest->employee->full_name, new LeaveApproved($leaveRequest));
    }

    public static function leaveRejected(LeaveRequest $leaveRequest): void
    {
        $leaveRequest->load(['employee','leaveType','reviewer']);
        $email = static::employeeEmail($leaveRequest->employee);
        if (!$email) return;

        static::send($email, $leaveRequest->employee->full_name, new LeaveRejected($leaveRequest));
    }

    public static function leaveCancelled(LeaveRequest $leaveRequest): void
    {
        $leaveRequest->load(['employee','leaveType']);
        $email = static::employeeEmail($leaveRequest->employee);
        if (!$email) return;

        static::send($email, $leaveRequest->employee->full_name, new LeaveCancelled($leaveRequest));
    }

    public static function payslipPublished(Payslip $payslip): void
    {
        $payslip->load(['employee','period']);
        $email = static::employeeEmail($payslip->employee);
        if (!$email) return;

        static::send($email, $payslip->employee->full_name, new PayslipPublished($payslip));
    }

    /**
     * Send payslip email to all employees when a payroll period is marked paid.
     */
    public static function payrollPeriodPaid(\App\Models\PayrollPeriod $period): void
    {
        $payslips = \App\Models\Payslip::with(['employee','period'])
            ->where('payroll_period_id', $period->id)
            ->where('status', 'paid')
            ->get();

        foreach ($payslips as $payslip) {
            static::payslipPublished($payslip);
        }
    }

    public static function passwordChanged(User $user): void
    {
        if (!$user->email) return;
        static::send($user->email, $user->name, new PasswordChanged($user));
    }

    public static function attendanceOverridden(Attendance $attendance): void
    {
        $attendance->load('employee');
        $email = static::employeeEmail($attendance->employee);
        if (!$email) return;

        static::send($email, $attendance->employee->full_name, new AttendanceOverridden($attendance));
    }

    public static function documentExpiryReminder(EmployeeDocument $document): void
    {
        $document->load('employee');
        $email = static::employeeEmail($document->employee);
        if (!$email) return;

        static::send($email, $document->employee->full_name, new DocumentExpiryReminder($document));
    }

    // ───────────────────────────────────────────────────────────
    protected static function notifyHrManagers(int $companyId, object $mailable, string $subject = ''): void
    {
        $hrManagers = User::where('company_id', $companyId)
            ->where('is_active', true)
            ->permission('leaves.approve')
            ->get();

        foreach ($hrManagers as $hr) {
            if ($hr->email) {
                static::send($hr->email, $hr->name, clone $mailable);
            }
        }
    }
}