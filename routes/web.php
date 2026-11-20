<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\AuditController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\AttendanceDeviceController;
use App\Http\Controllers\Admin\AttendanceEnrollmentController;
use App\Http\Controllers\Admin\ContractorController;
use App\Http\Controllers\AttendanceTerminalController;
use App\Http\Controllers\EmployeeDashboardController;
use App\Http\Controllers\EmployeeAttendanceController;
use App\Http\Controllers\Admin\ExcuseController;
use App\Http\Controllers\EmployeeRecruitmentController;
use App\Http\Controllers\EmployeeSelfServiceController;
use App\Http\Controllers\EmployeeNotificationController;
use App\Http\Controllers\Admin\WorkforceReportController;
use App\Http\Controllers\Admin\EmailCommunicationController;
use App\Http\Controllers\Admin\AssetController;
use App\Http\Controllers\Admin\StaffMovementController;
use App\Http\Controllers\Admin\ContractController;
use App\Http\Controllers\Admin\DirectoryController;
use App\Http\Controllers\Admin\ComplianceController;
use App\Http\Controllers\Admin\DepartmentCommunicationController;
use App\Http\Controllers\RecruitmentController;
use Illuminate\Support\Facades\Route;

// Attendance Terminal (public kiosk endpoint)
Route::get('/attendance-terminal', [AttendanceTerminalController::class, 'show'])->name('attendance.terminal');

// Public recruitment
Route::get('/careers', [RecruitmentController::class, 'index'])->name('recruitment.public.index');
Route::get('/careers/{vacancy}', [RecruitmentController::class, 'show'])->name('recruitment.public.show');
Route::post('/careers/{vacancy}/apply', [RecruitmentController::class, 'apply'])->middleware('throttle:10,1')->name('recruitment.public.apply');

// ── Guest Routes (Auth) ───────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/', fn() => redirect()->route('login'));
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    Route::get('/forgot-password', [PasswordResetController::class, 'request'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'email'])->name('password.email');
    Route::get('/reset-password/{token}', [PasswordResetController::class, 'form'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'reset'])->name('password.update');
});

// ── Authenticated Routes ──────────────────────────────────────────────────
Route::middleware(['auth', 'active.user'])->group(function () {

    // Logout
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard')
        ->middleware('can:view dashboard');

    Route::get('/employee/dashboard', [EmployeeDashboardController::class, 'index'])
        ->name('employee.dashboard')
        ->middleware('can:view dashboard');
    Route::get('/employee/profile', [EmployeeSelfServiceController::class, 'profile'])->name('employee.profile')->middleware('can:view dashboard');
    Route::patch('/employee/profile', [EmployeeSelfServiceController::class, 'updateProfile'])->name('employee.profile.update')->middleware('can:view dashboard');
    Route::get('/employee/profile.pdf', [EmployeeSelfServiceController::class, 'profilePdf'])->name('employee.profile.pdf')->middleware('can:view dashboard');
    Route::get('/employee/payslips', [EmployeeSelfServiceController::class, 'payslips'])->name('employee.payslips')->middleware('can:view dashboard');
    Route::get('/employee/notifications', [EmployeeNotificationController::class, 'index'])->name('employee.notifications')->middleware('can:view dashboard');
    Route::get('/employee/notifications/{id}/read', [EmployeeNotificationController::class, 'read'])->name('employee.notifications.read')->middleware('can:view dashboard');
    Route::post('/employee/notifications/read-all', [EmployeeNotificationController::class, 'readAll'])->name('employee.notifications.read-all')->middleware('can:view dashboard');

    Route::get('/excuses', [ExcuseController::class, 'index'])->name('excuses.index');
    Route::get('/excuses/create', [ExcuseController::class, 'create'])->name('excuses.create')->middleware('can:submit excuse');
    Route::post('/excuses', [ExcuseController::class, 'store'])->name('excuses.store')->middleware('can:submit excuse');
    Route::post('/excuses/{excuse}/approve', [ExcuseController::class, 'approve'])->name('excuses.approve')->middleware('can:approve excuses');
    Route::post('/excuses/{excuse}/reject', [ExcuseController::class, 'reject'])->name('excuses.reject')->middleware('can:approve excuses');

    Route::get('/employee/attendance', [EmployeeAttendanceController::class, 'show'])
        ->name('employee.attendance')
        ->middleware('can:view dashboard');

    Route::get('/employee/recruitment', [EmployeeRecruitmentController::class, 'index'])
        ->name('employee.recruitment')
        ->middleware('can:view internal vacancies');
    Route::post('/employee/recruitment/{vacancy}/apply', [EmployeeRecruitmentController::class, 'apply'])
        ->name('employee.recruitment.apply')
        ->middleware('can:apply vacancies');
    Route::post('/employee/attendance/punch', [EmployeeAttendanceController::class, 'punch'])
        ->name('employee.attendance.punch')
        ->middleware('can:view dashboard');

    // Employees
    Route::middleware('can:view employees')->group(function () {
        Route::get('/employees/import', [EmployeeController::class, 'import'])->name('employees.import');
        Route::get('/employees/{employee}/pdf', [EmployeeController::class, 'downloadPdf'])->name('employees.pdf');
        Route::resource('employees', EmployeeController::class);
        Route::get('/api/employees/search', [EmployeeController::class, 'search'])->name('employees.search');
        Route::post('/employees/{employee}/documents', [EmployeeController::class, 'storeDocument'])->name('employees.documents.store');
        Route::delete('/employees/{employee}/documents/{document}', [EmployeeController::class, 'destroyDocument'])->name('employees.documents.destroy');

        Route::get('/contractors', [ContractorController::class, 'index'])->name('contractors.index');
        Route::get('/contractors/{employee}', [ContractorController::class, 'show'])->name('contractors.show');
    });

    // Departments
    Route::middleware('can:view departments')->group(function () {
        Route::resource('departments', DepartmentController::class);
    });

    // Users & Roles
    Route::middleware('can:view users')->group(function () {
        Route::resource('users', UserController::class);
    });

    // Reports & Analytics
    Route::middleware('can:view reports')->group(function () {
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/analytics', [App\Http\Controllers\Admin\AnalyticsController::class, 'index'])->name('analytics.index');
        Route::get('/reports/workforce', [WorkforceReportController::class, 'index'])->name('reports.workforce');
        Route::get('/reports/workforce.csv', [WorkforceReportController::class, 'csv'])->name('reports.workforce.csv');
        Route::get('/reports/workforce.pdf', [WorkforceReportController::class, 'pdf'])->name('reports.workforce.pdf');
    });
    Route::middleware('can:manage email communications')->group(function () {
        Route::get('/communications/email', [EmailCommunicationController::class, 'index'])->name('communications.email');
        Route::post('/communications/email', [EmailCommunicationController::class, 'send'])->name('communications.email.send');
    });
    Route::middleware('can:manage department communications')->group(function () {
        Route::get('/communications/departments', [DepartmentCommunicationController::class, 'index'])->name('department-communications.index');
        Route::post('/communications/departments', [DepartmentCommunicationController::class, 'store'])->name('department-communications.store');
        Route::post('/communications/departments/{communication}/respond', [DepartmentCommunicationController::class, 'respond'])->name('department-communications.respond');
    });
    Route::get('/directory', [DirectoryController::class, 'index'])->name('directory.index')->middleware('can:view directory');
    Route::get('/contracts', [ContractController::class, 'index'])->name('contracts.index')->middleware('can:view contracts');
    Route::get('/contracts/renewals', [ComplianceController::class, 'renewals'])->name('contracts.renewals')->middleware('can:manage contract renewals');
    Route::post('/contracts/renewals', [ComplianceController::class, 'requestRenewal'])->name('contracts.renewals.store')->middleware('can:manage contract renewals');
    Route::post('/contracts/renewals/{renewal}/approve', [ComplianceController::class, 'renewApprove'])->name('contracts.renewals.approve')->middleware('can:manage contract renewals');
    Route::get('/grievances', [ComplianceController::class, 'grievances'])->name('grievances.index')->middleware('can:view grievances');
    Route::post('/grievances', [ComplianceController::class, 'grievanceStore'])->name('grievances.store')->middleware('can:manage grievances');
    Route::middleware('can:view assets')->group(function () {
        Route::get('/assets', [AssetController::class, 'index'])->name('assets.index');
        Route::post('/assets', [AssetController::class, 'store'])->name('assets.store')->middleware('can:manage assets');
        Route::post('/assets/{asset}/assign', [AssetController::class, 'assign'])->name('assets.assign')->middleware('can:manage assets');
    });
    Route::middleware('can:view staff movements')->group(function () {
        Route::get('/staff-movements', [StaffMovementController::class, 'index'])->name('staff-movements.index');
        Route::post('/staff-movements', [StaffMovementController::class, 'store'])->name('staff-movements.store')->middleware('can:approve staff movements');
        Route::post('/staff-movements/{movement}/approve', [StaffMovementController::class, 'approve'])->name('staff-movements.approve')->middleware('can:approve staff movements');
        Route::post('/staff-movements/{movement}/reject', [StaffMovementController::class, 'reject'])->name('staff-movements.reject')->middleware('can:approve staff movements');
    });

    // Audit Logs
    Route::middleware('can:view audit logs')->group(function () {
        Route::get('/audit', [AuditController::class, 'index'])->name('audit.index');
    });

    // System Settings
    Route::middleware('can:manage system settings')->group(function () {
        Route::get('/settings', [App\Http\Controllers\Admin\SettingsController::class, 'index'])->name('settings.index');
        Route::post('/settings', [App\Http\Controllers\Admin\SettingsController::class, 'update'])->name('settings.update');
    });

    // System Notifications
    Route::post('/notifications/mark-all-read', [App\Http\Controllers\Admin\NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
    Route::get('/notifications/{id}/read', [App\Http\Controllers\Admin\NotificationController::class, 'markAsRead'])->name('notifications.read');

    // Phase 2 stub routes (placeholder)
    Route::get('/leave/apply', [App\Http\Controllers\Admin\LeaveController::class, 'apply'])->name('leave.apply')->middleware('can:apply leave');
    Route::post('/leave/apply', [App\Http\Controllers\Admin\LeaveController::class, 'store'])->name('leave.store')->middleware('can:apply leave');

    Route::middleware('can:view leave')->group(function () {
        Route::get('/leave/approvals', [App\Http\Controllers\Admin\LeaveController::class, 'approvals'])->name('leave.approvals');
        Route::post('/leave/approvals/{id}/approve', [App\Http\Controllers\Admin\LeaveController::class, 'approve'])->name('leave.approve')->middleware('can:approve leave');
        Route::post('/leave/approvals/{id}/reject', [App\Http\Controllers\Admin\LeaveController::class, 'reject'])->name('leave.reject')->middleware('can:approve leave');
        Route::get('/leave/types', [App\Http\Controllers\Admin\LeaveController::class, 'types'])->name('leave.types')->middleware('can:manage leave types');
        Route::post('/leave/types', [App\Http\Controllers\Admin\LeaveController::class, 'storeType'])->name('leave.store-type')->middleware('can:manage leave types');
    });
    Route::middleware('can:view attendance')->group(function () {
        Route::get('/attendance', [App\Http\Controllers\Admin\AttendanceController::class, 'index'])->name('attendance.index');
        Route::get('/attendance/devices', [AttendanceDeviceController::class, 'index'])->name('attendance.devices');
        Route::post('/attendance/devices', [AttendanceDeviceController::class, 'store'])->name('attendance.devices.store');
        Route::post('/attendance/manual', [App\Http\Controllers\Admin\AttendanceController::class, 'store'])->name('attendance.store');
    });
    Route::middleware('can:view payroll')->group(function () {
        Route::get('/finance/dashboard', [App\Http\Controllers\Admin\PayrollController::class, 'dashboard'])->name('finance.dashboard');
        Route::get('/payroll', [App\Http\Controllers\Admin\PayrollController::class, 'index'])->name('payroll.index');
        Route::post('/payroll', [App\Http\Controllers\Admin\PayrollController::class, 'store'])->name('payroll.store');
        Route::get('/payroll/{id}', [App\Http\Controllers\Admin\PayrollController::class, 'show'])->name('payroll.show');
        Route::post('/payroll/{id}/approve', [App\Http\Controllers\Admin\PayrollController::class, 'approve'])->name('payroll.approve');
        Route::post('/payroll/{id}/review', [App\Http\Controllers\Admin\PayrollController::class, 'review'])->name('payroll.review');
        Route::post('/payroll/{id}/pay', [App\Http\Controllers\Admin\PayrollController::class, 'pay'])->name('payroll.pay');
        Route::get('/payroll/record/{id}', [App\Http\Controllers\Admin\PayrollController::class, 'payslip'])->name('payroll.payslip');
    });
    Route::middleware('can:view performance')->group(function () {
        Route::get('/performance', [App\Http\Controllers\Admin\PerformanceController::class, 'index'])->name('performance.index');
        Route::post('/performance', [App\Http\Controllers\Admin\PerformanceController::class, 'store'])->name('performance.store');
        Route::delete('/performance/{id}', [App\Http\Controllers\Admin\PerformanceController::class, 'destroy'])->name('performance.destroy');
    });
    Route::middleware('can:view training')->group(function () {
        Route::get('/training', [App\Http\Controllers\Admin\TrainingController::class, 'index'])->name('training.index');
        Route::post('/training', [App\Http\Controllers\Admin\TrainingController::class, 'store'])->name('training.store');
        Route::post('/training/enroll', [App\Http\Controllers\Admin\TrainingController::class, 'enroll'])->name('training.enroll');
        Route::post('/training/enrollment/{id}/complete', [App\Http\Controllers\Admin\TrainingController::class, 'complete'])->name('training.complete');
        Route::delete('/training/enrollment/{id}', [App\Http\Controllers\Admin\TrainingController::class, 'destroyEnrollment'])->name('training.destroy-enrollment');
    });
    Route::middleware('can:view vacancies')->group(function () {
        Route::get('/recruitment/vacancies', [App\Http\Controllers\Admin\VacancyController::class, 'index'])->name('recruitment.vacancies');
    });
    Route::middleware('can:create vacancies')->group(function () {
        Route::post('/recruitment/vacancies', [App\Http\Controllers\Admin\VacancyController::class, 'store'])->name('recruitment.vacancies.store');
    });
    Route::middleware('can:edit vacancies')->group(function () {
        Route::put('/recruitment/vacancies/{vacancy}', [App\Http\Controllers\Admin\VacancyController::class, 'update'])->name('recruitment.vacancies.update');
    });
    Route::middleware('can:delete vacancies')->group(function () {
        Route::delete('/recruitment/vacancies/{vacancy}', [App\Http\Controllers\Admin\VacancyController::class, 'destroy'])->name('recruitment.vacancies.destroy');
    });

    Route::middleware('can:view applications')->group(function () {
        Route::get('/recruitment/applications', [App\Http\Controllers\Admin\JobApplicationController::class, 'index'])->name('recruitment.applications');
    });
    Route::middleware('can:manage applications')->group(function () {
        Route::post('/recruitment/applications', [App\Http\Controllers\Admin\JobApplicationController::class, 'store'])->name('recruitment.applications.store');
        Route::put('/recruitment/applications/{id}/status', [App\Http\Controllers\Admin\JobApplicationController::class, 'updateStatus'])->name('recruitment.applications.status');
        Route::delete('/recruitment/applications/{id}', [App\Http\Controllers\Admin\JobApplicationController::class, 'destroy'])->name('recruitment.applications.destroy');
    });
    Route::middleware('can:schedule interviews')->group(function () {
        Route::post('/recruitment/applications/{application}/interviews', [App\Http\Controllers\Admin\InterviewController::class, 'store'])->name('recruitment.interviews.store');
    });
    Route::middleware('can:view recruitment reports')->group(function () {
        Route::get('/reports/recruitment', [App\Http\Controllers\Admin\RecruitmentReportController::class, 'index'])->name('recruitment.reports');
    });
    Route::middleware('can:export recruitment reports')->group(function () {
        Route::get('/reports/recruitment.xlsx', [App\Http\Controllers\Admin\RecruitmentReportController::class, 'excel'])->name('recruitment.reports.excel');
        Route::get('/reports/recruitment.pdf', [App\Http\Controllers\Admin\RecruitmentReportController::class, 'pdf'])->name('recruitment.reports.pdf');
    });
});
