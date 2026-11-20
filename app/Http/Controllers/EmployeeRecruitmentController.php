<?php

namespace App\Http\Controllers;

use App\Models\JobApplication;
use App\Models\Vacancy;
use App\Services\AuditService;
use App\Services\UserNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class EmployeeRecruitmentController extends Controller
{
    public function index(Request $request): View
    {
        $employee = $request->user()->employee;
        abort_unless($employee, 403, 'Employee record not found for this account.');

        $vacancies = Vacancy::with('department')
            ->where('status', 'Open')
            ->where(function ($query) {
                $query->whereNull('closing_date')->orWhereDate('closing_date', '>=', today());
            })
            ->latest()
            ->get();
        $applications = JobApplication::with('vacancy')
            ->where('employee_id', $employee->id)
            ->latest()
            ->get();

        return view('employee.recruitment.index', compact('employee', 'vacancies', 'applications'));
    }

    public function apply(Request $request, Vacancy $vacancy): RedirectResponse
    {
        $employee = $request->user()->employee;
        abort_unless($employee, 403, 'Employee record not found for this account.');
        abort_unless($vacancy->status === 'Open' && (!$vacancy->closing_date || $vacancy->closing_date->isFuture() || $vacancy->closing_date->isToday()), 404);

        $request->validate([
            'cover_letter' => 'required|string|max:5000',
            'resume' => 'nullable|file|max:5120|mimes:pdf,doc,docx',
        ]);

        $alreadyApplied = JobApplication::where('vacancy_id', $vacancy->id)
            ->where('employee_id', $employee->id)
            ->whereNotIn('status', ['Rejected'])
            ->exists();

        if ($alreadyApplied) {
            return back()->with('error', 'You already have an active application for this vacancy.');
        }

        $resumePath = null;
        if ($request->hasFile('resume')) {
            $file = $request->file('resume');
            $resumePath = $file->storeAs('recruitment/resumes', time() . '_' . $file->getClientOriginalName(), 'public');
        }

        $application = JobApplication::create([
            'vacancy_id' => $vacancy->id,
            'employee_id' => $employee->id,
            'applicant_name' => $employee->full_name,
            'email' => $employee->official_email ?: $employee->personal_email,
            'phone' => $employee->phone,
            'cover_letter' => $request->cover_letter,
            'resume_path' => $resumePath,
            'status' => 'Applied',
        ]);

        AuditService::log('created', JobApplication::class, $application->id, [], $application->toArray(), "Employee {$employee->full_name} applied for {$vacancy->title}");
        UserNotificationService::send($request->user(), 'application_submitted', 'Application submitted', "Your application for '{$vacancy->title}' was submitted.", ['url' => route('employee.recruitment')]);

        return back()->with('success', "Your application for '{$vacancy->title}' was submitted.");
    }
}