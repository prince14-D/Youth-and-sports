<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\InterviewScheduled;
use App\Models\Interview;
use App\Models\JobApplication;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Services\UserNotificationService;

class InterviewController extends Controller
{
    public function store(Request $request, JobApplication $application): RedirectResponse
    {
        $data = $request->validate([
            'scheduled_at' => 'required|date|after:now',
            'duration_minutes' => 'required|integer|min:15|max:480',
            'mode' => 'required|in:In person,Virtual,Phone',
            'location' => 'nullable|string|max:255',
            'meeting_url' => 'nullable|url|max:500',
            'interviewer' => 'required|string|max:150',
            'notes' => 'nullable|string|max:2000',
        ]);

        $interview = $application->interviews()->create($data);
        $application->update(['status' => 'Interviewed']);
        if (!$application->employee_id) {
            Mail::to($application->email)->queue(new InterviewScheduled($interview));
        }
        UserNotificationService::send($application->employee?->user, 'interview_scheduled', 'Interview scheduled', "Your interview for '{$application->vacancy->title}' is scheduled for {$interview->scheduled_at->format('d M Y H:i')}.", ['url' => route('employee.recruitment')]);

        return back()->with('success', 'Interview scheduled and applicant notified.');
    }
}