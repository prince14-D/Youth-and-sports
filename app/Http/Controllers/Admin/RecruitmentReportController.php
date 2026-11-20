<?php

namespace App\Http\Controllers\Admin;

use App\Exports\RecruitmentExport;
use App\Http\Controllers\Controller;
use App\Models\JobApplication;
use App\Models\Vacancy;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

class RecruitmentReportController extends Controller
{
    public function index(): View
    {
        $vacancies = Vacancy::withCount('applications')->with('applications')->latest()->get();
        $pipeline = JobApplication::selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');
        return view('reports.recruitment', compact('vacancies', 'pipeline'));
    }

    public function excel()
    {
        return Excel::download(new RecruitmentExport, 'recruitment-report.xlsx');
    }

    public function pdf()
    {
        $applications = JobApplication::with('vacancy')->latest()->get();
        return Pdf::loadView('reports.recruitment-pdf', compact('applications'))
            ->setPaper('a4', 'landscape')->download('recruitment-report.pdf');
    }
}
