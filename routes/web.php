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
use App\Http\Controllers\Admin\ApprovalCenterController;
use App\Http\Controllers\Admin\ContractorController;
use App\Http\Controllers\Admin\DmaDashboardController;
use App\Http\Controllers\Admin\DocumentWorkflowController;
use App\Http\Controllers\Admin\ExternalAttendanceImportController;
use App\Http\Controllers\Admin\FinanceDivisionController;
use App\Http\Controllers\Admin\HrAlertController;
use App\Http\Controllers\Admin\HrDataAnalystDashboardController;
use App\Http\Controllers\Admin\InternalAuditController;
use App\Http\Controllers\Admin\ProcurementController;
use App\Http\Controllers\Admin\StadiumManagementController;
use App\Http\Controllers\Admin\MinisterDashboardController;
use App\Http\Controllers\EmployeeDashboardController;
use App\Http\Controllers\StaffIdCardController;
use App\Http\Controllers\Admin\ExcuseController;
use App\Http\Controllers\EmployeeRecruitmentController;
use App\Http\Controllers\EmployeeSelfServiceController;
use App\Http\Controllers\EmployeeNotificationController;
use App\Http\Controllers\Admin\WorkforceReportController;
use App\Http\Controllers\Admin\EmailCommunicationController;
use App\Http\Controllers\Admin\AssetController;
use App\Http\Controllers\Admin\AssetDashboardController;
use App\Http\Controllers\Admin\StaffMovementController;
use App\Http\Controllers\Admin\ContractController;
use App\Http\Controllers\Admin\DirectoryController;
use App\Http\Controllers\Admin\ComplianceController;
use App\Http\Controllers\Admin\DepartmentCommunicationController;
use App\Http\Controllers\RecruitmentController;
use Illuminate\Support\Facades\Route;

// Attendance feature is disabled (Ministry uses an external working attendance system)
Route::get('/attendance-terminal', fn () => redirect()->route('login')->with('warning', 'The built-in attendance module is currently disabled.'))->name('attendance.terminal');

// Public recruitment
Route::get('/careers', [RecruitmentController::class, 'index'])->name('recruitment.public.index');
Route::get('/careers/{vacancy}', [RecruitmentController::class, 'show'])->name('recruitment.public.show');
Route::post('/careers/{vacancy}/apply', [RecruitmentController::class, 'apply'])->middleware('throttle:10,1')->name('recruitment.public.apply');

// Public Staff & Contractor ID Card QR Verification
Route::get('/verify-id/{employeeNo}/{hash}', [StaffIdCardController::class, 'verify'])->name('id-cards.verify');

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

    // Dashboard (role-aware router)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard')
        ->middleware('can:view dashboard');

    // Executive Office Dashboards (Minister & Deputy Minister for Administration — restricted to each role)
    Route::get('/minister/dashboard', [MinisterDashboardController::class, 'index'])
        ->name('minister.dashboard')
        ->middleware(['can:view minister dashboard', 'role:Minister']);
    Route::get('/dma/dashboard', [DmaDashboardController::class, 'index'])
        ->name('dma.dashboard')
        ->middleware(['can:view dma dashboard', 'role:Deputy Minister for Administration']);

    // Paperless Ministry Document & Request Workflow (with strict marked-office visibility & tracking query)
    Route::middleware('can:view document workflow')->group(function () {
        Route::get('/documents', [DocumentWorkflowController::class, 'index'])->name('documents.index');
        Route::get('/documents/track', [DocumentWorkflowController::class, 'track'])->name('documents.track');
        Route::post('/documents', [DocumentWorkflowController::class, 'store'])->name('documents.store')->middleware('can:create document workflow');
        Route::get('/documents/{document}', [DocumentWorkflowController::class, 'show'])->name('documents.show');
        Route::get('/documents/{document}/attachment', [DocumentWorkflowController::class, 'downloadAttachment'])->name('documents.attachment');
        Route::get('/documents/{document}/logs/{log}/attachment', [DocumentWorkflowController::class, 'downloadLogAttachment'])->name('documents.logs.attachment');
        Route::get('/documents/{document}/slip.pdf', [DocumentWorkflowController::class, 'movementSlipPdf'])->name('documents.slip-pdf');
        Route::post('/documents/{document}/receive', [DocumentWorkflowController::class, 'receive'])->name('documents.receive');
        Route::post('/documents/{document}/forward', [DocumentWorkflowController::class, 'forward'])->name('documents.forward');
        Route::post('/documents/{document}/action', [DocumentWorkflowController::class, 'action'])->name('documents.action');
        Route::post('/documents/{document}/feedback', [DocumentWorkflowController::class, 'feedback'])->name('documents.feedback');
        Route::post('/documents/{document}/resubmit', [DocumentWorkflowController::class, 'resubmit'])->name('documents.resubmit');
    });

    // Multi-Level Approval Center (HR Officer -> Assistant HR Director -> HR Director)
    Route::get('/approvals', [ApprovalCenterController::class, 'index'])
        ->name('approvals.index')
        ->middleware('can:view leave');

    // Automated Contract Expiry & Retirement Alerts Center
    Route::middleware('can:view employees')->group(function () {
        Route::get('/hr-alerts', [HrAlertController::class, 'index'])->name('hr-alerts.index');
        Route::post('/hr-alerts/run', [HrAlertController::class, 'runNow'])->name('hr-alerts.run');
    });

    // Printable Staff & Contractor ID Cards (authorized for HR/Admin + owning employee/contractor)
    Route::get('/my-id-card', [StaffIdCardController::class, 'myIdCard'])->name('id-cards.mine');
    Route::get('/id-cards/{employee}', [StaffIdCardController::class, 'show'])->name('id-cards.show');
    Route::get('/id-cards/{employee}/pdf', [StaffIdCardController::class, 'downloadPdf'])->name('id-cards.pdf');

    // HR Data Analyst Workspace (restricted to HR Data Analyst)
    Route::middleware(['can:create employees', 'role:HR Data Analyst'])->group(function () {
        Route::get('/hr-analyst/dashboard', [HrDataAnalystDashboardController::class, 'index'])->name('hr-analyst.dashboard');
        Route::post('/hr-analyst/contracts/{employee}/quick-update', [HrDataAnalystDashboardController::class, 'quickUpdateContract'])->name('hr-analyst.contracts.quick-update');
    });

    // Employee & Contractor Self-Service Portal
    Route::get('/employee/dashboard', [EmployeeDashboardController::class, 'index'])
        ->name('employee.dashboard')
        ->middleware('can:view dashboard');
    Route::get('/contractor/dashboard', [EmployeeDashboardController::class, 'contractorDashboard'])
        ->name('contractor.dashboard')
        ->middleware('can:view dashboard');
    Route::post('/contractor/renewal-request', [EmployeeDashboardController::class, 'submitRenewalRequest'])
        ->name('contractor.renewal-request')
        ->middleware('can:view dashboard');

    Route::get('/employee/profile', [EmployeeSelfServiceController::class, 'profile'])->name('employee.profile')->middleware('can:view dashboard');
    Route::patch('/employee/profile', [EmployeeSelfServiceController::class, 'updateProfile'])->name('employee.profile.update')->middleware('can:view dashboard');
    Route::get('/employee/profile.pdf', [EmployeeSelfServiceController::class, 'profilePdf'])->name('employee.profile.pdf')->middleware('can:view dashboard');
    Route::get('/employee/payslips', [EmployeeSelfServiceController::class, 'payslips'])->name('employee.payslips')->middleware('can:view dashboard');
    Route::get('/employee/notifications', [EmployeeNotificationController::class, 'index'])->name('employee.notifications')->middleware('can:view dashboard');
    Route::get('/employee/notifications/{id}/read', [EmployeeNotificationController::class, 'read'])->name('employee.notifications.read')->middleware('can:view dashboard');
    Route::post('/employee/notifications/read-all', [EmployeeNotificationController::class, 'readAll'])->name('employee.notifications.read-all')->middleware('can:view dashboard');

    // Payslip View & Official PDF (authorized per-record in controller for both Payroll staff & owning Employee/Contractor)
    Route::get('/payroll/record/{id}', [App\Http\Controllers\Admin\PayrollController::class, 'payslip'])->name('payroll.payslip');
    Route::get('/payroll/record/{id}/pdf', [App\Http\Controllers\Admin\PayrollController::class, 'payslipPdf'])->name('payroll.payslip.pdf');

    Route::get('/excuses', [ExcuseController::class, 'index'])->name('excuses.index');
    Route::get('/excuses/create', [ExcuseController::class, 'create'])->name('excuses.create')->middleware('can:submit excuse');
    Route::post('/excuses', [ExcuseController::class, 'store'])->name('excuses.store')->middleware('can:submit excuse');
    Route::post('/excuses/{excuse}/approve', [ExcuseController::class, 'approve'])->name('excuses.approve')->middleware('can:approve excuses');
    Route::post('/excuses/{excuse}/reject', [ExcuseController::class, 'reject'])->name('excuses.reject')->middleware('can:approve excuses');

    Route::get('/employee/attendance', fn () => redirect()->route('dashboard')->with('warning', 'The built-in attendance feature is currently disabled.'))
        ->name('employee.attendance');
    Route::post('/employee/attendance/punch', fn () => redirect()->route('dashboard')->with('warning', 'The built-in attendance feature is currently disabled.'))
        ->name('employee.attendance.punch');

    Route::get('/employee/recruitment', [EmployeeRecruitmentController::class, 'index'])
        ->name('employee.recruitment')
        ->middleware('can:view internal vacancies');
    Route::post('/employee/recruitment/{vacancy}/apply', [EmployeeRecruitmentController::class, 'apply'])
        ->name('employee.recruitment.apply')
        ->middleware('can:apply vacancies');

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
        Route::post('/users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status')->middleware('can:edit users');
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
    Route::post('/contracts/renewals/{renewal}/reject', [ComplianceController::class, 'renewReject'])->name('contracts.renewals.reject')->middleware('can:manage contract renewals');
    Route::get('/grievances', [ComplianceController::class, 'grievances'])->name('grievances.index')->middleware('can:view grievances');
    Route::post('/grievances', [ComplianceController::class, 'grievanceStore'])->name('grievances.store')->middleware('can:manage grievances');
    Route::middleware('can:view assets')->group(function () {
        Route::get('/assets/dashboard', [AssetDashboardController::class, 'index'])->name('assets.dashboard')->middleware('role:Asset Manager');
        Route::get('/assets/export.csv', [AssetDashboardController::class, 'exportCsv'])->name('assets.export.csv');
        Route::post('/assets/maintenance', [AssetDashboardController::class, 'storeMaintenance'])->name('assets.maintenance.store')->middleware('can:manage assets');
        Route::get('/assets', [AssetController::class, 'index'])->name('assets.index');
        Route::post('/assets', [AssetController::class, 'store'])->name('assets.store')->middleware('can:manage assets');
        Route::post('/assets/{asset}/assign', [AssetController::class, 'assign'])->name('assets.assign')->middleware('can:manage assets');
        Route::post('/assets/{asset}/return', [AssetController::class, 'returnAsset'])->name('assets.return')->middleware('can:manage assets');
        Route::post('/assets/{asset}/status', [AssetController::class, 'updateStatus'])->name('assets.status')->middleware('can:manage assets');
        Route::post('/assets/requests', [AssetController::class, 'storeRequest'])->name('assets.requests.store')->middleware('can:manage asset requests');
        Route::post('/assets/requests/{assetRequest}/review', [AssetController::class, 'reviewRequest'])->name('assets.requests.review');
    });
    Route::middleware('can:view staff movements')->group(function () {
        Route::get('/staff-movements', [StaffMovementController::class, 'index'])->name('staff-movements.index');
        Route::post('/staff-movements', [StaffMovementController::class, 'store'])->name('staff-movements.store')->middleware('can:approve staff movements');
        Route::post('/staff-movements/{movement}/approve', [StaffMovementController::class, 'approve'])->name('staff-movements.approve')->middleware('can:approve staff movements');
        Route::post('/staff-movements/{movement}/reject', [StaffMovementController::class, 'reject'])->name('staff-movements.reject')->middleware('can:approve staff movements');
    });

    // Audit Logs & Internal Audit Division
    Route::middleware('can:view audit logs')->group(function () {
        Route::get('/audit', [AuditController::class, 'index'])->name('audit.index');
    });

    Route::middleware(['can:view internal audit', 'role:Auditor|Minister|Deputy Minister for Administration'])->group(function () {
        Route::get('/internal-audit/dashboard', [InternalAuditController::class, 'dashboard'])->name('internal-audit.dashboard');
        Route::get('/internal-audit/report.pdf', [InternalAuditController::class, 'reportPdf'])->name('internal-audit.report.pdf');
        Route::get('/internal-audit/findings.csv', [InternalAuditController::class, 'exportFindingsCsv'])->name('internal-audit.findings.csv');
        Route::post('/internal-audit/transactions/{transaction}/verify', [InternalAuditController::class, 'verifyTransaction'])
            ->name('internal-audit.transactions.verify')
            ->middleware('can:manage internal audit');
        Route::post('/internal-audit/findings', [InternalAuditController::class, 'storeFinding'])
            ->name('internal-audit.findings.store')
            ->middleware('can:manage internal audit');
        Route::post('/internal-audit/findings/{finding}/status', [InternalAuditController::class, 'updateFindingStatus'])
            ->name('internal-audit.findings.status')
            ->middleware('can:manage internal audit');
        Route::post('/internal-audit/assessments', [InternalAuditController::class, 'storeAssessment'])
            ->name('internal-audit.assessments.store')
            ->middleware('can:manage internal audit');
    });

    // System Settings
    Route::middleware('can:manage system settings')->group(function () {
        Route::get('/settings', [App\Http\Controllers\Admin\SettingsController::class, 'index'])->name('settings.index');
        Route::post('/settings', [App\Http\Controllers\Admin\SettingsController::class, 'update'])->name('settings.update');
        Route::post('/settings/mail', [App\Http\Controllers\Admin\SettingsController::class, 'updateMail'])->name('settings.mail.update');
        Route::post('/settings/test-email', [App\Http\Controllers\Admin\SettingsController::class, 'sendTestEmail'])->name('settings.test-email');
    });

    // Procurement Unit: tenders, bidding, bid evaluation, supplier registry & compliance
    Route::middleware(['can:view procurement', 'role:Procurement Officer|Minister|Deputy Minister for Administration|Auditor'])->prefix('procurement')->name('procurement.')->group(function () {
        Route::get('/dashboard', [ProcurementController::class, 'dashboard'])->name('dashboard');
        Route::get('/report.pdf', [ProcurementController::class, 'reportPdf'])->name('report.pdf');
        Route::get('/tenders.csv', [ProcurementController::class, 'exportTendersCsv'])->name('tenders.csv');
        Route::get('/tenders/{tender}', [ProcurementController::class, 'showTender'])->name('tenders.show');
        Route::get('/tenders/{tender}/evaluation.pdf', [ProcurementController::class, 'tenderPdf'])->name('tenders.pdf');

        Route::middleware('can:manage procurement')->group(function () {
            Route::post('/tenders', [ProcurementController::class, 'storeTender'])->name('tenders.store');
            Route::post('/tenders/{tender}/status', [ProcurementController::class, 'updateStatus'])->name('tenders.status');
            Route::post('/tenders/{tender}/bids', [ProcurementController::class, 'storeBid'])->name('bids.store');
            Route::post('/tenders/{tender}/award', [ProcurementController::class, 'award'])->name('tenders.award');
            Route::post('/bids/{bid}/score', [ProcurementController::class, 'scoreBid'])->name('bids.score');
            Route::post('/suppliers', [ProcurementController::class, 'storeSupplier'])->name('suppliers.store');
            Route::post('/suppliers/{supplier}/status', [ProcurementController::class, 'updateSupplierStatus'])->name('suppliers.status');
            Route::post('/suppliers/{supplier}/evaluations', [ProcurementController::class, 'storeEvaluation'])->name('suppliers.evaluations.store');
        });
    });

    // Stadium Management: facilities, event preparation, maintenance work orders & safety inspections
    Route::middleware(['can:view stadium management', 'role:Stadium Manager|Minister|Deputy Minister for Administration|Auditor'])->prefix('stadium')->name('stadium.')->group(function () {
        Route::get('/dashboard', [StadiumManagementController::class, 'dashboard'])->name('dashboard');
        Route::get('/report.pdf', [StadiumManagementController::class, 'reportPdf'])->name('report.pdf');
        Route::get('/maintenance.csv', [StadiumManagementController::class, 'exportMaintenanceCsv'])->name('maintenance.csv');
        Route::get('/events/{event}/clearance.pdf', [StadiumManagementController::class, 'eventClearancePdf'])->name('events.clearance.pdf');

        Route::middleware('can:manage stadium management')->group(function () {
            Route::post('/facilities', [StadiumManagementController::class, 'storeFacility'])->name('facilities.store');
            Route::post('/facilities/{facility}/status', [StadiumManagementController::class, 'updateFacilityStatus'])->name('facilities.status');
            Route::post('/events', [StadiumManagementController::class, 'storeEvent'])->name('events.store');
            Route::post('/events/{event}/readiness', [StadiumManagementController::class, 'updateEventReadiness'])->name('events.readiness');
            Route::post('/maintenance', [StadiumManagementController::class, 'storeMaintenance'])->name('maintenance.store');
            Route::post('/maintenance/{maintenance}/status', [StadiumManagementController::class, 'updateMaintenanceStatus'])->name('maintenance.status');
            Route::post('/safety-inspections', [StadiumManagementController::class, 'storeSafetyInspection'])->name('safety.store');
        });
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
    // Attendance module disabled (external attendance system in use)
    Route::get('/attendance', fn () => redirect()->route('dashboard')->with('warning', 'The built-in attendance module is currently disabled.'))->name('attendance.index');
    Route::get('/attendance/devices', fn () => redirect()->route('dashboard')->with('warning', 'The built-in attendance module is currently disabled.'))->name('attendance.devices');

    Route::middleware('can:view payroll')->group(function () {
        // Finance Division Command Center, Budgets, Payment Vouchers, Accounting Ledger & Financial Reports
        Route::get('/finance/dashboard', [FinanceDivisionController::class, 'dashboard'])->name('finance.dashboard')->middleware('role:Finance Officer|Auditor');
        Route::get('/finance/ledger', [FinanceDivisionController::class, 'ledger'])->name('finance.ledger');
        Route::get('/finance/ledger.csv', [FinanceDivisionController::class, 'exportLedgerCsv'])->name('finance.ledger.csv');
        Route::get('/finance/report.pdf', [FinanceDivisionController::class, 'financialReportPdf'])->name('finance.report.pdf');
        Route::post('/finance/budgets', [FinanceDivisionController::class, 'storeBudget'])->name('finance.budgets.store');
        Route::post('/finance/transactions', [FinanceDivisionController::class, 'storeTransaction'])->name('finance.transactions.store');
        Route::post('/finance/transactions/{transaction}/status', [FinanceDivisionController::class, 'updateStatus'])->name('finance.transactions.status');
        Route::get('/finance/vouchers/{transaction}/pdf', [FinanceDivisionController::class, 'voucherPdf'])->name('finance.vouchers.pdf');

        Route::get('/payroll', [App\Http\Controllers\Admin\PayrollController::class, 'index'])->name('payroll.index');
        Route::post('/payroll', [App\Http\Controllers\Admin\PayrollController::class, 'store'])->name('payroll.store');

        // External Attendance Summary Bridge (must be before /payroll/{id})
        Route::get('/payroll/external-attendance', [ExternalAttendanceImportController::class, 'index'])->name('external-attendance.index');
        Route::get('/payroll/external-attendance/template.csv', [ExternalAttendanceImportController::class, 'downloadTemplate'])->name('external-attendance.template');
        Route::post('/payroll/external-attendance/import', [ExternalAttendanceImportController::class, 'import'])->name('external-attendance.import');
        Route::post('/payroll/external-attendance/apply-to-payroll', [ExternalAttendanceImportController::class, 'applyToPayroll'])->name('external-attendance.apply-payroll');

        // Itemized Payroll Allowances & Deductions
        Route::post('/payroll/record/{recordId}/line-items', [App\Http\Controllers\Admin\PayrollController::class, 'storeLineItem'])->name('payroll.line-items.store');
        Route::delete('/payroll/line-items/{itemId}', [App\Http\Controllers\Admin\PayrollController::class, 'destroyLineItem'])->name('payroll.line-items.destroy');

        // Bank & Treasury Payment Schedules
        Route::get('/payroll/{id}/bank-schedule.csv', [App\Http\Controllers\Admin\PayrollController::class, 'bankScheduleCsv'])->name('payroll.bank-schedule.csv');
        Route::get('/payroll/{id}/bank-schedule.pdf', [App\Http\Controllers\Admin\PayrollController::class, 'bankSchedulePdf'])->name('payroll.bank-schedule.pdf');

        Route::get('/payroll/{id}', [App\Http\Controllers\Admin\PayrollController::class, 'show'])->name('payroll.show');
        Route::post('/payroll/{id}/approve', [App\Http\Controllers\Admin\PayrollController::class, 'approve'])->name('payroll.approve');
        Route::post('/payroll/{id}/reject', [App\Http\Controllers\Admin\PayrollController::class, 'reject'])->name('payroll.reject');
        Route::post('/payroll/{id}/review', [App\Http\Controllers\Admin\PayrollController::class, 'review'])->name('payroll.review');
        Route::post('/payroll/{id}/pay', [App\Http\Controllers\Admin\PayrollController::class, 'pay'])->name('payroll.pay');
        Route::post('/payroll/{id}/email-payslips', [App\Http\Controllers\Admin\PayrollController::class, 'emailAllPayslips'])->name('payroll.email-all');
        Route::post('/payroll/record/{id}/email', [App\Http\Controllers\Admin\PayrollController::class, 'emailSinglePayslip'])->name('payroll.payslip.email');
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
