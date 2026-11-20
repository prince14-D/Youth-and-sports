<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PerformanceAppraisal;
use App\Models\Employee;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PerformanceController extends Controller
{
    public function index(): View
    {
        $appraisals = PerformanceAppraisal::with(['employee', 'appraiser'])
            ->orderBy('appraisal_year', 'desc')
            ->get();

        $employees = Employee::active()->orderBy('first_name')->get();

        // Stats calculation
        $avgScore = $appraisals->avg('score') ?: 0;
        $ratingsCount = [
            'Excellent'    => $appraisals->where('rating', 'Excellent')->count(),
            'Good'         => $appraisals->where('rating', 'Good')->count(),
            'Satisfactory' => $appraisals->where('rating', 'Satisfactory')->count(),
            'Poor'         => $appraisals->where('rating', 'Poor')->count(),
        ];

        return view('performance.index', compact('appraisals', 'employees', 'avgScore', 'ratingsCount'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'employee_id'    => 'required|exists:employees,id',
            'appraisal_year' => 'required|integer|min:2000|max:' . (now()->year + 1),
            'score'          => 'required|numeric|min:0|max:100',
            'comments'       => 'nullable|string|max:1000',
            'recommendations'=> 'nullable|string|max:1000',
        ]);

        $score = $request->score;
        
        // Map rating based on score
        if ($score >= 85) {
            $rating = 'Excellent';
        } elseif ($score >= 70) {
            $rating = 'Good';
        } elseif ($score >= 50) {
            $rating = 'Satisfactory';
        } else {
            $rating = 'Poor';
        }

        $appraisal = PerformanceAppraisal::create([
            'employee_id'     => $request->employee_id,
            'appraiser_id'    => auth()->id(),
            'appraisal_year'  => $request->appraisal_year,
            'appraisal_period'=> $request->appraisal_year . ' Annual',
            'score'           => $score,
            'rating'          => $rating,
            'comments'        => $request->comments,
            'recommendations' => $request->recommendations,
            'status'          => 'Approved',
        ]);

        $employee = Employee::find($request->employee_id);

        AuditService::log('created', PerformanceAppraisal::class, $appraisal->id,
            [], $appraisal->toArray(),
            "Logged annual performance appraisal for {$employee->full_name}. Score: {$score} ({$rating})"
        );

        return redirect()->route('performance.index')
            ->with('success', "Performance appraisal for {$employee->full_name} logged successfully.");
    }

    public function destroy($id): RedirectResponse
    {
        $app = PerformanceAppraisal::findOrFail($id);
        $name = $app->employee->full_name;
        $app->delete();

        AuditService::log('deleted', PerformanceAppraisal::class, $id,
            [], [],
            "Deleted performance appraisal for {$name}"
        );

        return redirect()->route('performance.index')
            ->with('success', "Performance appraisal for {$name} deleted successfully.");
    }
}
