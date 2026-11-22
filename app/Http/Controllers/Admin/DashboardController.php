<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\EmployeeDashboardController;
use App\Models\JobApplication;
use App\Models\Vacancy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        if ($user && $user->hasRole('Minister') && !$user->hasRole('System Administrator')) {
            return app(MinisterDashboardController::class)->index($request);
        }

        if ($user && $user->hasRole('Deputy Minister for Administration') && !$user->hasRole('System Administrator')) {
            return app(DmaDashboardController::class)->index($request);
        }

        if ($user && $user->hasRole('Asset Manager') && !$user->hasAnyRole(['System Administrator', 'Minister', 'Deputy Minister for Administration', 'Human Resource Director'])) {
            return app(AssetDashboardController::class)->index($request);
        }

        if ($user && $user->hasRole('Finance Officer') && !$user->hasAnyRole(['System Administrator', 'Minister', 'Deputy Minister for Administration', 'Human Resource Director'])) {
            return app(FinanceDivisionController::class)->dashboard($request);
        }

        if ($user && $user->hasRole('Procurement Officer') && !$user->hasAnyRole(['System Administrator', 'Minister', 'Deputy Minister for Administration', 'Human Resource Director'])) {
            return app(ProcurementController::class)->dashboard($request);
        }

        if ($user && $user->hasRole('Stadium Manager') && !$user->hasAnyRole(['System Administrator', 'Minister', 'Deputy Minister for Administration', 'Human Resource Director'])) {
            return app(StadiumManagementController::class)->dashboard($request);
        }

        if ($user && $user->hasRole('Auditor') && !$user->hasAnyRole(['System Administrator', 'Minister', 'Deputy Minister for Administration', 'Human Resource Director'])) {
            return app(InternalAuditController::class)->dashboard($request);
        }

        if ($user && $user->hasRole('HR Data Analyst') && !$user->hasAnyRole(['System Administrator', 'Human Resource Director', 'Assistant HR Director', 'HR Officer'])) {
            return app(HrDataAnalystDashboardController::class)->index($request);
        }

        if ($user && $user->hasRole('Contractor') && !$user->hasAnyRole(['System Administrator', 'Human Resource Director', 'Assistant HR Director', 'HR Officer', 'HR Data Analyst'])) {
            return app(EmployeeDashboardController::class)->contractorDashboard($request);
        }

        if ($user && $user->hasRole('Employee') && !$user->hasAnyRole(['System Administrator', 'Human Resource Director', 'Assistant HR Director', 'HR Officer', 'HR Data Analyst', 'Payroll Officer', 'Finance Officer', 'Auditor', 'Department Head', 'Director/Manager'])) {
            if ($user->employee && $user->employee->isContractor()) {
                return app(EmployeeDashboardController::class)->contractorDashboard($request);
            }
            return app(EmployeeDashboardController::class)->index($request);
        }

        $recentApplicants = JobApplication::where('applicant_name', 'not like', 'Mock%')
            ->latest()
            ->take(4)
            ->get();

        $vacancies = Vacancy::withCount('applications')
            ->latest()
            ->get();

        return view('dashboard.index', compact('recentApplicants', 'vacancies'));
    }
}
