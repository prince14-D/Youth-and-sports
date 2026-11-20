<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JobApplication;
use App\Models\Vacancy;
use App\Mail\ApplicationStatusChanged;
use App\Services\AuditService;
use App\Services\UserNotificationService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class JobApplicationController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->get('status');
        $vacancyId = $request->get('vacancy_id');

        $query = JobApplication::with(['vacancy.department', 'employee']);

        if ($status) {
            $query->where('status', $status);
        }
        if ($vacancyId) {
            $query->where('vacancy_id', $vacancyId);
        }

        $applications = $query->orderBy('created_at', 'desc')->get();
        $vacancies = Vacancy::orderBy('title')->get();

        return view('recruitment.applications', compact('applications', 'vacancies', 'status', 'vacancyId'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'vacancy_id'     => [
                'required',
                Rule::exists('vacancies', 'id')->where(function ($query) {
                    $query->where('status', 'Open')
                        ->whereNull('deleted_at')
                        ->where(function ($query) {
                            $query->whereNull('closing_date')
                                ->orWhereDate('closing_date', '>=', today());
                        });
                }),
            ],
            'applicant_name' => 'required|string|max:100',
            'email'          => 'required|email|max:100',
            'phone'          => 'nullable|string|max:20',
            'resume'         => 'nullable|file|max:5120|mimes:pdf,doc,docx',
        ]);

        $data = $request->only(['vacancy_id', 'applicant_name', 'email', 'phone']);
        $data['status'] = 'Applied';

        if ($request->hasFile('resume')) {
            $file = $request->file('resume');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('recruitment/resumes', $filename, 'public');
            $data['resume_path'] = $path;
        }

        $app = JobApplication::create($data);
        $vacancy = Vacancy::findOrFail($request->vacancy_id);

        AuditService::log('created', JobApplication::class, $app->id,
            [], $app->toArray(),
            "Submitted job application from '{$app->applicant_name}' for vacancy: {$vacancy->title}"
        );

        return back()->with('success', "Application for '{$app->applicant_name}' submitted successfully.");
    }

    public function updateStatus(Request $request, $id): RedirectResponse
    {
        $request->validate([
            'status' => 'required|in:Applied,Shortlisted,Interviewed,Offered,Hired,Rejected',
        ]);

        $app = JobApplication::findOrFail($id);
        $old = $app->toArray();

        $app->update([
            'status' => $request->status,
        ]);

        AuditService::log('updated', JobApplication::class, $app->id,
            $old, $app->fresh()->toArray(),
            "Updated application status for '{$app->applicant_name}' to: {$request->status}"
        );
        if (!$app->employee_id) {
            Mail::to($app->email)->queue(new ApplicationStatusChanged($app, $request->status));
        }
        UserNotificationService::send($app->employee?->user, 'application_status', 'Application status updated', "Your application for '{$app->vacancy->title}' is now {$request->status}.", ['url' => route('employee.recruitment')]);

        return back()->with('success', "Application status for '{$app->applicant_name}' updated to {$request->status}.");
    }

    public function destroy($id): RedirectResponse
    {
        $app = JobApplication::findOrFail($id);
        $name = $app->applicant_name;

        if ($app->resume_path) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($app->resume_path);
        }

        $app->delete();

        AuditService::log('deleted', JobApplication::class, $id,
            [], [],
            "Deleted job application from '{$name}'"
        );

        return back()->with('success', "Application from '{$name}' has been deleted.");
    }
}
