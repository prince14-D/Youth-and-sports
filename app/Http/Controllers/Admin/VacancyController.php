<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Vacancy;
use App\Models\Department;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class VacancyController extends Controller
{
    public function index(): View
    {
        $vacancies = Vacancy::with('department')
            ->withCount('applications')
            ->orderBy('created_at', 'desc')
            ->get();

        $departments = Department::active()->orderBy('name')->get();

        return view('recruitment.vacancies', compact('vacancies', 'departments'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title'         => 'required|string|max:100',
            'department_id' => 'required|exists:departments,id',
            'description'   => 'nullable|string',
            'requirements'  => 'nullable|string',
            'positions'     => 'required|integer|min:1',
            'closing_date'  => 'nullable|date|after_or_equal:today',
            'status'        => 'required|in:Draft,Open,Closed,Filled',
        ]);

        $vacancy = Vacancy::create($data);

        AuditService::log('created', Vacancy::class, $vacancy->id,
            [], $vacancy->toArray(),
            "Posted new job vacancy: {$vacancy->title}"
        );

        return back()->with('success', "Job vacancy '{$vacancy->title}' posted successfully.");
    }

    public function update(Request $request, Vacancy $vacancy): RedirectResponse
    {
        $old = $vacancy->toArray();
        $data = $request->validate([
            'title'         => 'required|string|max:100',
            'department_id' => 'required|exists:departments,id',
            'description'   => 'nullable|string',
            'requirements'  => 'nullable|string',
            'positions'     => 'required|integer|min:1',
            'closing_date'  => 'nullable|date',
            'status'        => 'required|in:Draft,Open,Closed,Filled',
        ]);

        $vacancy->update($data);

        AuditService::log('updated', Vacancy::class, $vacancy->id,
            $old, $vacancy->fresh()->toArray(),
            "Updated job vacancy: {$vacancy->title}"
        );

        return back()->with('success', "Job vacancy '{$vacancy->title}' updated successfully.");
    }

    public function destroy(Vacancy $vacancy): RedirectResponse
    {
        $title = $vacancy->title;
        $vacancy->delete();

        AuditService::log('deleted', Vacancy::class, $vacancy->id,
            [], [],
            "Deleted job vacancy: {$title}"
        );

        return back()->with('success', "Job vacancy '{$title}' removed.");
    }
}
