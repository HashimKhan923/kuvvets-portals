<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\RecruitmentController;
use App\Http\Controllers\LeaveController;
use App\Http\Controllers\PayrollController;
use App\Http\Controllers\PerformanceController;
use App\Http\Controllers\TrainingController;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\Employee\AuthController as EmployeeAuthController;
use App\Http\Controllers\Employee\DashboardController as EmployeeDashboardController;

use App\Http\Controllers\Employee\AuthController as EmployeeAuthCtrl;
use App\Http\Controllers\Employee\AttendanceController as EmpAttendanceCtrl;
use App\Http\Controllers\Employee\AttendanceHistoryController as EmpHistoryCtrl;
use App\Http\Controllers\Employee\BreakController as EmpBreakCtrl;
use App\Http\Controllers\Employee\DashboardController as EmpDashboardCtrl;
use App\Http\Controllers\Employee\LeaveController as EmpLeaveCtrl;
use App\Http\Controllers\Employee\PayslipController as EmpPayslipCtrl;
use App\Http\Controllers\Employee\ProfileController as EmpProfileCtrl;


/*
|--------------------------------------------------------------------------
| ROOT
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    if (auth()->check()) {
        return auth()->user()->isAdmin()
            ? redirect()->route('dashboard')
            : redirect()->route('employee.dashboard');
    }
    return redirect()->route('employee.login');
});

Route::get('/login', function () {
    return redirect()->route('admin.login');
})->name('login');


/*
|==========================================================================
| ADMIN PORTAL
|==========================================================================
*/

/*
|--------------------------------------------------------------------------
| Admin Auth — Guests only
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/login',  [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.post');
});

/*
|--------------------------------------------------------------------------
| Admin Logout
|--------------------------------------------------------------------------
*/
Route::post('/admin/logout', [LoginController::class, 'logout'])
    ->name('logout')
    ->middleware('auth');

/*
|--------------------------------------------------------------------------
| Admin Protected Routes (with permission-level locking)
|--------------------------------------------------------------------------
*/
Route::get('/admin/locations/{location}/qr.svg', [\App\Http\Controllers\LocationController::class, 'qrSvg'])
    ->name('locations.qr');
Route::middleware(['auth', 'admin.portal'])->prefix('admin')->group(function () {

    // ── DASHBOARD ─────────────────────────────────────────────
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->middleware('permission:dashboard.view')
        ->name('dashboard');

    // ── EMPLOYEES ─────────────────────────────────────────────
    Route::prefix('employees')->name('employees.')->group(function () {

        Route::middleware('permission:employees.create')->group(function () {
            Route::get('/create',                [EmployeeController::class, 'create'])->name('create');
            Route::post('/',                     [EmployeeController::class, 'store'])->name('store');
        });
        Route::middleware('permission:employees.view')->group(function () {
            Route::get('/',                      [EmployeeController::class, 'index'])->name('index');
            Route::get('/{employee}',            [EmployeeController::class, 'show'])->name('show');
        });

        Route::middleware('permission:employees.edit')->group(function () {
            Route::get('/{employee}/edit',       [EmployeeController::class, 'edit'])->name('edit');
            Route::put('/{employee}',            [EmployeeController::class, 'update'])->name('update');
            Route::post('/{employee}/notes',     [EmployeeController::class, 'addNote'])->name('notes.store');
        });
        Route::middleware('permission:employees.delete')->group(function () {
            Route::delete('/{employee}',         [EmployeeController::class, 'destroy'])->name('destroy');
        });
        Route::middleware('permission:documents.manage')->group(function () {
            Route::post('/{employee}/documents', [EmployeeController::class, 'uploadDocument'])->name('documents.upload');
        });
    });

    // ── DEPARTMENTS ───────────────────────────────────────────
    Route::prefix('departments')->name('departments.')->group(function () {
        Route::middleware('permission:departments.manage')->group(function () {
            Route::get('/',                              [DepartmentController::class, 'index'])->name('index');
            Route::post('/',                             [DepartmentController::class, 'store'])->name('store');
            Route::get('/{department}',                  [DepartmentController::class, 'show'])->name('show');
            Route::put('/{department}',                  [DepartmentController::class, 'update'])->name('update');
            Route::delete('/{department}',               [DepartmentController::class, 'destroy'])->name('destroy');
            Route::post('/{department}/toggle',          [DepartmentController::class, 'toggleStatus'])->name('toggle');
            Route::post('/{department}/designations',    [DepartmentController::class, 'storeDesignation'])->name('designations.store');
            Route::delete('/designations/{designation}', [DepartmentController::class, 'destroyDesignation'])->name('designations.destroy');
        });
    });

    // ── ATTENDANCE ────────────────────────────────────────────
    Route::prefix('attendance')->name('attendance.')->group(function () {
        Route::middleware('permission:attendance.view')->group(function () {
            Route::get('/',              [AttendanceController::class, 'index'])->name('index');
        });
        Route::middleware('permission:attendance.report')->group(function () {
            Route::get('/report',        [AttendanceController::class, 'report'])->name('report');
        });
        Route::middleware('permission:attendance.manage')->group(function () {
            Route::post('/store',        [AttendanceController::class, 'store'])->name('store');
            Route::get('/shifts',        [AttendanceController::class, 'shifts'])->name('shifts');
            Route::post('/shifts',       [AttendanceController::class, 'storeShift'])->name('shifts.store');
            Route::post('/assign-shift', [AttendanceController::class, 'assignShift'])->name('shifts.assign');
        });
    });

    // ── RECRUITMENT ───────────────────────────────────────────
    Route::prefix('recruitment')->name('recruitment.')->group(function () {
        Route::middleware('permission:recruitment.view')->group(function () {
            Route::get('/',                                   [RecruitmentController::class, 'index'])->name('index');
            Route::get('/jobs',                               [RecruitmentController::class, 'jobs'])->name('jobs');
            Route::get('/jobs/{jobPosting}',                  [RecruitmentController::class, 'showJob'])->name('jobs.show');
            Route::get('/applicants/{applicant}',             [RecruitmentController::class, 'showApplicant'])->name('applicants.show');
        });
        Route::middleware('permission:recruitment.manage')->group(function () {
            Route::get('/jobs/create',                        [RecruitmentController::class, 'createJob'])->name('jobs.create');
            Route::post('/jobs',                              [RecruitmentController::class, 'storeJob'])->name('jobs.store');
            Route::patch('/jobs/{jobPosting}/status',         [RecruitmentController::class, 'updateJobStatus'])->name('jobs.status');
            Route::post('/jobs/{jobPosting}/applicants',      [RecruitmentController::class, 'storeApplicant'])->name('applicants.store');
            Route::patch('/applicants/{applicant}/stage',     [RecruitmentController::class, 'moveStage'])->name('applicants.stage');
            Route::patch('/applicants/{applicant}/rate',      [RecruitmentController::class, 'rateApplicant'])->name('applicants.rate');
            Route::patch('/applicants/{applicant}/hire',      [RecruitmentController::class, 'markHired'])->name('applicants.hire');
            Route::post('/applicants/{applicant}/interviews', [RecruitmentController::class, 'scheduleInterview'])->name('interviews.store');
            Route::patch('/interviews/{interview}/result',    [RecruitmentController::class, 'recordInterview'])->name('interviews.result');
            Route::post('/applicants/{applicant}/offer',      [RecruitmentController::class, 'sendOffer'])->name('offer.send');
            Route::patch('/offers/{offer}/respond',           [RecruitmentController::class, 'respondOffer'])->name('offer.respond');
        });
    });

    // ── LEAVES ────────────────────────────────────────────────
    Route::prefix('leaves')->name('leaves.')->group(function () {

        // Management pages (static routes — must be before wildcards)
        Route::middleware('permission:leaves.manage')->group(function () {
            Route::get('/manage/balances',              [LeaveController::class, 'balances'])->name('balances');
            Route::post('/manage/allocate',             [LeaveController::class, 'allocate'])->name('allocate');
            Route::post('/manage/grant-extra',          [LeaveController::class, 'grantExtra'])->name('grant-extra');
            Route::get('/manage/types',                 [LeaveController::class, 'types'])->name('types');
            Route::post('/manage/types',                [LeaveController::class, 'storeType'])->name('types.store');
            Route::get('/manage/holidays',              [LeaveController::class, 'holidays'])->name('holidays');
            Route::post('/manage/holidays',             [LeaveController::class, 'storeHoliday'])->name('holidays.store');
            Route::delete('/manage/holidays/{holiday}', [LeaveController::class, 'destroyHoliday'])->name('holidays.destroy');
            Route::get('/manage/calendar',              [LeaveController::class, 'calendar'])->name('calendar');
        });
        Route::middleware('permission:leaves.report')->group(function () {
            Route::get('/manage/report',                [LeaveController::class, 'report'])->name('report');
        });

        // View
        Route::middleware('permission:leaves.view')->group(function () {
            Route::get('/',                        [LeaveController::class, 'index'])->name('index');
            Route::get('/{leaveRequest}',          [LeaveController::class, 'show'])->name('show');
        });

        // Apply
        Route::middleware('permission:leaves.apply')->group(function () {
            Route::get('/create',                  [LeaveController::class, 'create'])->name('create');
            Route::post('/',                       [LeaveController::class, 'store'])->name('store');
            Route::post('/{leaveRequest}/cancel',  [LeaveController::class, 'cancel'])->name('cancel');
        });

        // Approve / reject
        Route::middleware('permission:leaves.approve')->group(function () {
            Route::post('/{leaveRequest}/approve', [LeaveController::class, 'approve'])->name('approve');
            Route::post('/{leaveRequest}/reject',  [LeaveController::class, 'reject'])->name('reject');
        });
    });

    // ── PAYROLL ───────────────────────────────────────────────
    Route::prefix('payroll')->name('payroll.')->group(function () {
        Route::middleware('permission:payroll.view')->group(function () {
            Route::get('/',                                [PayrollController::class, 'index'])->name('index');
            Route::get('/periods/{period}',                [PayrollController::class, 'period'])->name('period');
            Route::get('/payslips/{payslip}',              [PayrollController::class, 'showPayslip'])->name('payslip.show');
            Route::get('/salary-structures',               [PayrollController::class, 'salaryStructures'])->name('salary-structures');
            Route::get('/tax-calculator',                  [PayrollController::class, 'taxCalculator'])->name('tax-calculator');
            Route::post('/tax-calculator/calculate',       [PayrollController::class, 'calculateTax'])->name('tax-calculator.calculate');
            Route::get('/report',                          [PayrollController::class, 'report'])->name('report');
        });
        Route::middleware('permission:payslip.download')->group(function () {
            Route::get('/payslips/{payslip}/pdf',          [PayrollController::class, 'downloadPdf'])->name('payslip.pdf');
        });
        Route::middleware('permission:payroll.process')->group(function () {
            Route::post('/periods',                        [PayrollController::class, 'createPeriod'])->name('periods.create');
            Route::post('/periods/{period}/generate',      [PayrollController::class, 'generate'])->name('generate');
            Route::post('/periods/{period}/adjustment',    [PayrollController::class, 'addAdjustment'])->name('adjustment');
            Route::post('/payslips/{payslip}/recalculate', [PayrollController::class, 'recalculate'])->name('payslip.recalculate');
            Route::post('/salary-structures/{employee}',   [PayrollController::class, 'saveSalaryStructure'])->name('salary-structures.save');
        });
        Route::middleware('permission:payroll.approve')->group(function () {
            Route::post('/periods/{period}/approve',       [PayrollController::class, 'approve'])->name('approve');
            Route::post('/periods/{period}/mark-paid',     [PayrollController::class, 'markPaid'])->name('mark-paid');
        });
        Route::middleware('permission:payroll.export')->group(function () {
            Route::get('/periods/{period}/export',         [PayrollController::class, 'bankExport'])->name('export');
        });
    });

    // ── PERFORMANCE ───────────────────────────────────────────
    Route::prefix('performance')->name('performance.')->group(function () {
        Route::middleware('permission:performance.view')->group(function () {
            Route::get('/',                              [PerformanceController::class, 'index'])->name('index');
            Route::get('/cycles',                        [PerformanceController::class, 'cycles'])->name('cycles');
            Route::get('/cycles/{cycle}',                [PerformanceController::class, 'showCycle'])->name('cycle');
            Route::get('/appraisals/{appraisal}',        [PerformanceController::class, 'showAppraisal'])->name('appraisal');
            Route::get('/kpis',                          [PerformanceController::class, 'kpis'])->name('kpis');
            Route::get('/report',                        [PerformanceController::class, 'report'])->name('report');
        });
        Route::middleware('permission:performance.manage')->group(function () {
            Route::post('/cycles',                       [PerformanceController::class, 'storeCycle'])->name('cycles.store');
            Route::patch('/cycles/{cycle}/status',       [PerformanceController::class, 'updateCycleStatus'])->name('cycles.status');
            Route::post('/cycles/{cycle}/generate',      [PerformanceController::class, 'generateAppraisals'])->name('cycles.generate');
            Route::post('/cycles/{cycle}/goals',         [PerformanceController::class, 'assignGoal'])->name('goals.assign');
            Route::patch('/goals/{goal}',                [PerformanceController::class, 'updateGoal'])->name('goals.update');
            Route::post('/kpis',                         [PerformanceController::class, 'storeKpi'])->name('kpis.store');
        });
        Route::middleware('permission:performance.review')->group(function () {
            Route::post('/appraisals/{appraisal}',       [PerformanceController::class, 'submitAppraisal'])->name('appraisal.submit');
        });
    });

    // ── TRAINING ──────────────────────────────────────────────
    Route::prefix('training')->name('training.')->group(function () {
        Route::middleware('permission:training.view')->group(function () {
            Route::get('/',                                    [TrainingController::class, 'index'])->name('index');
            Route::get('/programs',                            [TrainingController::class, 'programs'])->name('programs');
            Route::get('/sessions',                            [TrainingController::class, 'sessions'])->name('sessions');
            Route::get('/sessions/{session}',                  [TrainingController::class, 'showSession'])->name('session');
            Route::get('/certifications',                      [TrainingController::class, 'certifications'])->name('certifications');
            Route::get('/skill-matrix',                        [TrainingController::class, 'skillMatrix'])->name('skill-matrix');
            Route::get('/report',                              [TrainingController::class, 'report'])->name('report');
        });
        Route::middleware('permission:training.manage')->group(function () {
            Route::post('/programs',                           [TrainingController::class, 'storeProgram'])->name('programs.store');
            Route::get('/sessions/create',                     [TrainingController::class, 'createSession'])->name('sessions.create');
            Route::post('/sessions',                           [TrainingController::class, 'storeSession'])->name('sessions.store');
            Route::patch('/sessions/{session}/status',         [TrainingController::class, 'updateSessionStatus'])->name('sessions.status');
            Route::post('/sessions/{session}/enroll',          [TrainingController::class, 'enroll'])->name('sessions.enroll');
            Route::post('/sessions/{session}/attendance',      [TrainingController::class, 'markAttendance'])->name('sessions.attendance');
            Route::post('/skills',                             [TrainingController::class, 'storeSkill'])->name('skills.store');
            Route::post('/skills/update',                      [TrainingController::class, 'updateSkill'])->name('skills.update');
        });
    });

    // ── ASSETS ────────────────────────────────────────────────
    Route::prefix('assets')->name('assets.')->group(function () {
        Route::middleware('permission:assets.view')->group(function () {
            Route::get('/',                                        [AssetController::class, 'index'])->name('index');
            Route::get('/list',                                    [AssetController::class, 'list'])->name('list');
            Route::get('/{asset}',                                 [AssetController::class, 'show'])->name('show')->whereNumber('asset');
            Route::get('/manage/categories',                       [AssetController::class, 'categories'])->name('categories');
            Route::get('/manage/maintenance',                      [AssetController::class, 'maintenance'])->name('maintenance');
            Route::get('/manage/report',                           [AssetController::class, 'report'])->name('report');
        });
        Route::middleware('permission:assets.manage')->group(function () {
            Route::get('/create',                                  [AssetController::class, 'create'])->name('create');
            Route::post('/',                                       [AssetController::class, 'store'])->name('store');
            Route::get('/{asset}/edit',                            [AssetController::class, 'edit'])->name('edit');
            Route::put('/{asset}',                                 [AssetController::class, 'update'])->name('update');
            Route::post('/{asset}/maintenance',                    [AssetController::class, 'storeMaintenance'])->name('maintenance.store');
            Route::post('/maintenance/{record}/complete',          [AssetController::class, 'completeMaintenance'])->name('maintenance.complete');
            Route::post('/{asset}/rental',                         [AssetController::class, 'storeRental'])->name('rental.store');
            Route::post('/manage/categories',                      [AssetController::class, 'storeCategory'])->name('categories.store');
        });
        Route::middleware('permission:assets.assign')->group(function () {
            Route::post('/{asset}/assign',                         [AssetController::class, 'assign'])->name('assign');
            Route::post('/assignments/{assignment}/return',        [AssetController::class, 'returnAsset'])->name('return');
        });
    });

    // ── DOCUMENTS ─────────────────────────────────────────────
    Route::prefix('documents')->name('documents.')->group(function () {
        Route::middleware('permission:documents.view')->group(function () {
            Route::get('/',                                     [DocumentController::class, 'index'])->name('index');
            Route::get('/list',                                 [DocumentController::class, 'list'])->name('list');
            Route::get('/manage/categories',                    [DocumentController::class, 'categories'])->name('categories');
            Route::get('/employee/{employee}',                  [DocumentController::class, 'employeeDocuments'])->name('employee');
            Route::get('/api/search',                           [DocumentController::class, 'search'])->name('search');
            Route::get('/{document}',                           [DocumentController::class, 'show'])->name('show')->whereNumber('document');
            Route::get('/{document}/download',                  [DocumentController::class, 'download'])->name('download');
        });
        Route::middleware('permission:documents.manage')->group(function () {
            Route::post('/upload',                              [DocumentController::class, 'upload'])->name('upload');
            Route::put('/{document}',                           [DocumentController::class, 'update'])->name('update');
            Route::post('/{document}/version',                  [DocumentController::class, 'uploadVersion'])->name('version');
            Route::post('/manage/categories',                   [DocumentController::class, 'storeCategory'])->name('categories.store');
        });
        Route::middleware('permission:documents.delete')->group(function () {
            Route::delete('/{document}',                        [DocumentController::class, 'destroy'])->name('destroy');
        });
    });

    // ── REPORTS ───────────────────────────────────────────────
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::middleware('permission:reports.hr|reports.attendance|reports.payroll|reports.all')->group(function () {
            Route::get('/',            [ReportController::class, 'index'])->name('index');
        });
        Route::middleware('permission:reports.hr|reports.all')->group(function () {
            Route::get('/workforce',   [ReportController::class, 'workforce'])->name('workforce');
            Route::get('/export/employees', [ReportController::class, 'exportEmployeesCsv'])->name('export.employees');
        });
        Route::middleware('permission:reports.attendance|reports.all')->group(function () {
            Route::get('/attendance',  [ReportController::class, 'attendance'])->name('attendance');
        });
        Route::middleware('permission:leaves.report|reports.all')->group(function () {
            Route::get('/leave',       [ReportController::class, 'leave'])->name('leave');
        });
        Route::middleware('permission:reports.payroll|reports.all')->group(function () {
            Route::get('/payroll',     [ReportController::class, 'payroll'])->name('payroll');
        });
        Route::middleware('permission:reports.hr|reports.all')->group(function () {
            Route::get('/performance', [ReportController::class, 'performance'])->name('performance');
        });
    });

    // ── SETTINGS ──────────────────────────────────────────────
    Route::prefix('settings')->name('settings.')->group(function () {

        // "My Profile" is always allowed
        Route::get('/profile',                 [SettingController::class, 'profile'])->name('profile');
        Route::post('/profile',                [SettingController::class, 'updateProfile'])->name('profile.update');
        Route::post('/profile/password',       [SettingController::class, 'updatePassword'])->name('profile.password');

        Route::middleware('permission:settings.view')->group(function () {
            Route::get('/',                        [SettingController::class, 'index'])->name('index');
            Route::get('/company',                 [SettingController::class, 'company'])->name('company');
            Route::get('/hr',                      [SettingController::class, 'hr'])->name('hr');
            Route::get('/payroll',                 [SettingController::class, 'payroll'])->name('payroll');
        });
        Route::middleware('permission:settings.manage')->group(function () {
            Route::post('/company',                [SettingController::class, 'updateCompany'])->name('company.update');
            Route::post('/hr',                     [SettingController::class, 'updateHr'])->name('hr.update');
            Route::post('/payroll',                [SettingController::class, 'updatePayroll'])->name('payroll.update');
        });
        Route::middleware('permission:users.manage')->group(function () {
            Route::get('/users',                   [SettingController::class, 'users'])->name('users');
            Route::post('/users',                  [SettingController::class, 'storeUser'])->name('users.store');
            Route::put('/users/{user}',            [SettingController::class, 'updateUser'])->name('users.update');
            Route::post('/users/{user}/toggle',    [SettingController::class, 'toggleUser'])->name('users.toggle');
            Route::delete('/users/{user}',         [SettingController::class, 'destroyUser'])->name('users.destroy');
        });
        Route::middleware('permission:roles.manage')->group(function () {
            Route::get('/roles',                                    [SettingController::class, 'roles'])->name('roles');
            Route::post('/roles',                                   [SettingController::class, 'storeRole'])->name('roles.store');
            Route::post('/roles/{role}/permissions',                [SettingController::class, 'updateRolePermissions'])->name('roles.permissions');
            Route::delete('/roles/{role}',                          [SettingController::class, 'destroyRole'])->name('roles.destroy');
            Route::post('/permissions',                             [SettingController::class, 'storePermission'])->name('permissions.store');
        });
        Route::middleware('permission:audit_logs.view')->group(function () {
            Route::get('/audit-log',               [SettingController::class, 'auditLog'])->name('audit-log');
        });
    });


        // ── LOCATIONS ─────────────────────────────────────────────
    Route::prefix('locations')->name('locations.')->group(function () {
        Route::middleware('permission:locations.view')->group(function () {
            Route::get('/',                          [\App\Http\Controllers\LocationController::class, 'index'])->name('index');
            Route::get('/{location}',                [\App\Http\Controllers\LocationController::class, 'show'])->name('show');
            // Route::get('/{location}/qr.svg',         [\App\Http\Controllers\LocationController::class, 'qrSvg'])->name('qr');
            Route::get('/{location}/print',          [\App\Http\Controllers\LocationController::class, 'print'])->name('print');
        });
        Route::middleware('permission:locations.manage')->group(function () {
            Route::get('/create/new',                [\App\Http\Controllers\LocationController::class, 'create'])->name('create');
            Route::post('/',                         [\App\Http\Controllers\LocationController::class, 'store'])->name('store');
            Route::get('/{location}/edit',           [\App\Http\Controllers\LocationController::class, 'edit'])->name('edit');
            Route::put('/{location}',                [\App\Http\Controllers\LocationController::class, 'update'])->name('update');
            Route::post('/{location}/rotate-qr',     [\App\Http\Controllers\LocationController::class, 'rotateQr'])->name('rotate-qr');
        });
        Route::middleware('permission:locations.assign')->group(function () {
            Route::post('/{location}/assign',                        [\App\Http\Controllers\LocationController::class, 'assign'])->name('assign');
            Route::delete('/{location}/employees/{employee}',        [\App\Http\Controllers\LocationController::class, 'unassign'])->name('unassign');
        });
        Route::middleware('permission:locations.delete')->group(function () {
            Route::delete('/{location}',             [\App\Http\Controllers\LocationController::class, 'destroy'])->name('destroy');
        });
    });

}); // END admin middleware group


/*
|==========================================================================
| EMPLOYEE PORTAL
|==========================================================================
*/

Route::middleware('guest')->prefix('employee')->name('employee.')->group(function () {
    Route::get('/login',  [EmployeeAuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [EmployeeAuthController::class, 'login'])->name('login.post');
});

Route::post('/employee/logout', [EmployeeAuthController::class, 'logout'])
    ->name('employee.logout')
    ->middleware('auth');

Route::middleware(['auth', 'employee.portal'])->prefix('employee')->name('employee.')->group(function () {

    Route::get('/dashboard', [EmployeeDashboardController::class, 'index'])->name('dashboard');

    // Attendance — Check-in/out API
    Route::post('/attendance/check-in',     [\App\Http\Controllers\Employee\AttendanceController::class, 'checkIn'])->name('attendance.check-in');
    Route::post('/attendance/check-out',    [\App\Http\Controllers\Employee\AttendanceController::class, 'checkOut'])->name('attendance.check-out');
    Route::get('/attendance/status',        [\App\Http\Controllers\Employee\AttendanceController::class, 'status'])->name('attendance.status');

    // Breaks
    Route::post('/attendance/break/start',  [\App\Http\Controllers\Employee\BreakController::class, 'start'])->name('attendance.break.start');
    Route::post('/attendance/break/end',    [\App\Http\Controllers\Employee\BreakController::class, 'end'])->name('attendance.break.end');

    // Attendance history
    Route::get('/attendance',               [\App\Http\Controllers\Employee\AttendanceHistoryController::class, 'index'])->name('attendance.index');
    Route::get('/attendance/day/{date}',    [\App\Http\Controllers\Employee\AttendanceHistoryController::class, 'dayDetail'])->name('attendance.day');
    Route::get('/attendance/export',        [\App\Http\Controllers\Employee\AttendanceHistoryController::class, 'export'])->name('attendance.export');

     // ── Leaves ──
    Route::get('/leaves',[\App\Http\Controllers\Employee\LeaveController::class, 'index'])->name('leaves.index');
    Route::get('/leaves/apply',[\App\Http\Controllers\Employee\LeaveController::class, 'create'])->name('leaves.create');
    Route::post('/leaves',[\App\Http\Controllers\Employee\LeaveController::class, 'store'])->name('leaves.store');
    Route::get('/leaves/calculate',[\App\Http\Controllers\Employee\LeaveController::class, 'calculate'])->name('leaves.calculate');
    Route::get('/leaves/{leaveRequest}',[\App\Http\Controllers\Employee\LeaveController::class, 'show'])->name('leaves.show');
    Route::post('/leaves/{leaveRequest}/cancel',[\App\Http\Controllers\Employee\LeaveController::class, 'cancel'])->name('leaves.cancel');
    Route::get('/leaves/{leaveRequest}/document',[\App\Http\Controllers\Employee\LeaveController::class, 'downloadDocument'])->name('leaves.document');

    // ── Payslips ──
    Route::get('/payslips',                      [\App\Http\Controllers\Employee\PayslipController::class, 'index'])->name('payslips.index');
    Route::get('/payslips/{payslip}',            [\App\Http\Controllers\Employee\PayslipController::class, 'show'])->name('payslips.show');
    Route::get('/payslips/{payslip}/pdf',        [\App\Http\Controllers\Employee\PayslipController::class, 'pdf'])->name('payslips.pdf');

    // ── Profile & Documents ──
    Route::get('/profile',                                  [\App\Http\Controllers\Employee\ProfileController::class, 'index'])->name('profile.index');
    Route::put('/profile',                                  [\App\Http\Controllers\Employee\ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/avatar',                          [\App\Http\Controllers\Employee\ProfileController::class, 'updateAvatar'])->name('profile.avatar');
    Route::delete('/profile/avatar',                        [\App\Http\Controllers\Employee\ProfileController::class, 'removeAvatar'])->name('profile.avatar.remove');
    Route::put('/profile/password',                         [\App\Http\Controllers\Employee\ProfileController::class, 'updatePassword'])->name('profile.password');

    Route::post('/profile/documents',                       [\App\Http\Controllers\Employee\ProfileController::class, 'uploadDocument'])->name('profile.documents.upload');
    Route::get('/profile/documents/{document}/download',    [\App\Http\Controllers\Employee\ProfileController::class, 'downloadDocument'])->name('profile.documents.download');
    Route::delete('/profile/documents/{document}',          [\App\Http\Controllers\Employee\ProfileController::class, 'deleteDocument'])->name('profile.documents.destroy');

    // documents.index alias (for sidebar)
    Route::redirect('/documents', '/employee/profile?tab=documents')->name('documents.index');
});

/// Mobile API routes for employee portal (using Sanctum for authentication)
Route::prefix('mobile-api/employee')->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class])->group(function () {

    Route::post('/login', [\App\Http\Controllers\Employee\AuthController::class, 'apiLogin']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout',                   [\App\Http\Controllers\Employee\AuthController::class,              'apiLogout']);
        Route::get('/me',                        [\App\Http\Controllers\Employee\DashboardController::class,         'apiMe']);
        Route::get('/dashboard',                 [\App\Http\Controllers\Employee\DashboardController::class,         'apiIndex']);
        Route::get('/attendance/status',         [\App\Http\Controllers\Employee\AttendanceController::class,        'status']);
        Route::post('/check-in',                 [\App\Http\Controllers\Employee\AttendanceController::class,        'checkIn']);
        Route::post('/check-out',                [\App\Http\Controllers\Employee\AttendanceController::class,        'checkOut']);
        Route::get('/attendance/history',        [\App\Http\Controllers\Employee\AttendanceHistoryController::class, 'apiIndex']);
        Route::post('/break/start',              [\App\Http\Controllers\Employee\BreakController::class,             'start']);
        Route::post('/break/end',                [\App\Http\Controllers\Employee\BreakController::class,             'end']);
        Route::get('/leaves',                    [\App\Http\Controllers\Employee\LeaveController::class,             'apiIndex']);
        Route::post('/leaves',                   [\App\Http\Controllers\Employee\LeaveController::class,             'store']);
        Route::delete('/leaves/{leaveRequest}',  [\App\Http\Controllers\Employee\LeaveController::class,             'cancel']);
        Route::get('/payslips',                  [\App\Http\Controllers\Employee\PayslipController::class,           'apiIndex']);
        Route::get('/profile',                   [\App\Http\Controllers\Employee\ProfileController::class,           'apiShow']);
    });
});