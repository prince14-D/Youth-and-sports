<?php

namespace App\Http\Controllers;

use App\Mail\ApplicationReceived;
use App\Models\JobApplication;
use App\Models\Vacancy;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class RecruitmentController extends Controller
{
    public function index(): View
    {
        $vacancies = Vacancy::with('department')
            ->where('status', 'Open')
            ->where(function ($query) {
                $query->whereNull('closing_date')->orWhereDate('closing_date', '>=', today());
            })->latest()->get();

        return view('recruitment.public.index', compact('vacancies'));
    }

    public function show(Vacancy $vacancy): View
    {
        abort_unless($vacancy->status === 'Open' && (!$vacancy->closing_date || $vacancy->closing_date->isToday() || $vacancy->closing_date->isFuture()), 404);
        return view('recruitment.public.show', compact('vacancy'));
    }

    public function apply(Request $request, Vacancy $vacancy): RedirectResponse
    {
        abort_unless($vacancy->status === 'Open' && (!$vacancy->closing_date || $vacancy->closing_date->isToday() || $vacancy->closing_date->isFuture()), 404);

        $data = $request->validate([
            'applicant_name' => 'required|string|max:100',
            'email' => 'required|email|max:100',
            'phone' => 'nullable|string|max:20',
            'cover_letter' => 'nullable|string|max:5000',
            'resume' => 'required|file|max:5120|mimes:pdf,doc,docx',
            'consent' => 'accepted',
        ]);

        $data['vacancy_id'] = $vacancy->id;
        $data['status'] = 'Applied';
        $data['resume_path'] = $request->file('resume')->store('recruitment/resumes', 'public');
        unset($data['resume'], $data['consent']);

        $application = JobApplication::create($data);
        Mail::to($application->email)->queue(new ApplicationReceived($application));

        return back()->with('success', 'Your application was submitted successfully. A confirmation email is on its way.');
    }
}
