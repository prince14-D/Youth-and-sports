<?php

namespace App\Exports;

use App\Models\JobApplication;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class RecruitmentExport implements FromCollection, WithHeadings
{
    public function collection(): Collection
    {
        return JobApplication::with('vacancy')->latest()->get()->map(fn ($application) => [
            $application->vacancy?->title,
            $application->applicant_name,
            $application->email,
            $application->phone,
            $application->status,
            $application->created_at->format('Y-m-d'),
        ]);
    }

    public function headings(): array
    {
        return ['Vacancy', 'Applicant', 'Email', 'Phone', 'Status', 'Applied On'];
    }
}
