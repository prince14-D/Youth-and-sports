<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\ExternalAttendanceSummary;
use App\Models\PayrollLineItem;
use App\Models\PayrollPeriod;
use App\Models\PayrollRecord;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExternalAttendanceImportController extends Controller
{
    public function index(Request $request): View
    {
        $selectedMonth = $request->query('month', now()->format('Y-m'));

        $availableMonths = ExternalAttendanceSummary::select('period_month')
            ->distinct()
            ->orderByDesc('period_month')
            ->pluck('period_month');

        if ($availableMonths->isNotEmpty() && !$request->filled('month') && !$availableMonths->contains($selectedMonth)) {
            $selectedMonth = $availableMonths->first();
        }

        $summaries = ExternalAttendanceSummary::with(['employee.department', 'period', 'importer'])
            ->where('period_month', $selectedMonth)
            ->orderByDesc('updated_at')
            ->get();

        $draftPeriods = PayrollPeriod::where('status', 'Draft')
            ->orderByDesc('start_date')
            ->get();

        $stats = [
            'total_staff'    => $summaries->count(),
            'avg_rate'       => $summaries->count() > 0 ? round($summaries->avg(fn ($s) => $s->attendance_rate), 1) : 0,
            'total_absent'   => $summaries->sum('days_absent'),
            'total_overtime' => $summaries->sum('overtime_hours'),
        ];

        return view('payroll.external-attendance', compact(
            'summaries',
            'selectedMonth',
            'availableMonths',
            'draftPeriods',
            'stats'
        ));
    }

    public function downloadTemplate()
    {
        $employees = Employee::active()->orderBy('employee_no')->get(['employee_no', 'first_name', 'last_name', 'employment_type']);

        return response()->streamDownload(function () use ($employees) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['employee_no', 'employee_name', 'working_days', 'days_present', 'days_absent', 'days_late', 'overtime_hours', 'notes']);
            foreach ($employees as $emp) {
                fputcsv($out, [
                    $emp->employee_no,
                    $emp->full_name,
                    22,
                    22,
                    0,
                    0,
                    0,
                    '',
                ]);
            }
            fclose($out);
        }, 'MYS-External-Attendance-Template-' . now()->format('Y-m') . '.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function import(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'period_month'      => ['required', 'regex:/^\d{4}-\d{2}$/'],
            'source_system'     => 'nullable|string|max:100',
            'payroll_period_id' => 'nullable|exists:payroll_periods,id',
            'csv_file'          => 'required|file|max:5120|mimes:csv,txt',
        ]);

        $handle = fopen($request->file('csv_file')->getRealPath(), 'r');
        if (!$handle) {
            return back()->with('error', 'Unable to open uploaded CSV file.');
        }

        $header = fgetcsv($handle);
        if (!$header) {
            fclose($handle);
            return back()->with('error', 'CSV file is empty.');
        }

        $normalizedHeader = array_map(fn ($h) => strtolower(trim((string) preg_replace('/^\xEF\xBB\xBF/', '', $h))), $header);
        $empNoIdx = array_search('employee_no', $normalizedHeader, true);
        if ($empNoIdx === false) {
            fclose($handle);
            return back()->with('error', 'CSV must include an "employee_no" column. Please download the template first.');
        }

        $idx = [
            'working_days'   => array_search('working_days', $normalizedHeader, true),
            'days_present'   => array_search('days_present', $normalizedHeader, true),
            'days_absent'    => array_search('days_absent', $normalizedHeader, true),
            'days_late'      => array_search('days_late', $normalizedHeader, true),
            'overtime_hours' => array_search('overtime_hours', $normalizedHeader, true),
            'notes'          => array_search('notes', $normalizedHeader, true),
        ];

        $imported = 0;
        $skipped = [];

        while (($row = fgetcsv($handle)) !== false) {
            $empNo = trim((string) ($row[$empNoIdx] ?? ''));
            if ($empNo === '') {
                continue;
            }

            $employee = Employee::where('employee_no', $empNo)->first();
            if (!$employee) {
                $skipped[] = $empNo;
                continue;
            }

            $workingDays = max(1, (int) ($idx['working_days'] !== false ? ($row[$idx['working_days']] ?? 22) : 22));
            $present     = max(0, (int) ($idx['days_present'] !== false ? ($row[$idx['days_present']] ?? 0) : 0));
            $absent      = max(0, (int) ($idx['days_absent'] !== false ? ($row[$idx['days_absent']] ?? 0) : 0));
            $late        = max(0, (int) ($idx['days_late'] !== false ? ($row[$idx['days_late']] ?? 0) : 0));
            $overtime    = max(0, (float) ($idx['overtime_hours'] !== false ? ($row[$idx['overtime_hours']] ?? 0) : 0));
            $notes       = $idx['notes'] !== false ? trim((string) ($row[$idx['notes']] ?? '')) : null;

            ExternalAttendanceSummary::updateOrCreate(
                [
                    'employee_id'  => $employee->id,
                    'period_month' => $data['period_month'],
                ],
                [
                    'payroll_period_id' => $data['payroll_period_id'] ?: null,
                    'working_days'      => $workingDays,
                    'days_present'      => $present,
                    'days_absent'       => $absent,
                    'days_late'         => $late,
                    'overtime_hours'    => $overtime,
                    'source_system'     => trim((string) ($data['source_system'] ?: 'External Attendance System')),
                    'notes'             => $notes ?: null,
                    'imported_by'       => $request->user()->id,
                ]
            );
            $imported++;
        }
        fclose($handle);

        AuditService::log(
            'external_attendance_imported',
            null,
            null,
            [],
            ['period_month' => $data['period_month'], 'imported' => $imported, 'skipped' => count($skipped)],
            "Imported {$imported} external attendance summary records for {$data['period_month']}"
        );

        $msg = "Imported {$imported} staff attendance summary record(s) for {$data['period_month']}.";
        if (count($skipped) > 0) {
            return redirect()->route('external-attendance.index', ['month' => $data['period_month']])
                ->with('warning', $msg . ' Unmatched Employee IDs skipped: ' . implode(', ', array_slice($skipped, 0, 8)));
        }

        return redirect()->route('external-attendance.index', ['month' => $data['period_month']])
            ->with('success', $msg);
    }

    public function applyToPayroll(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'period_month'          => ['required', 'regex:/^\d{4}-\d{2}$/'],
            'payroll_period_id'     => 'required|exists:payroll_periods,id',
            'apply_overtime'        => 'nullable|boolean',
            'overtime_rate_lrd'     => 'nullable|numeric|min:0|max:100000',
            'apply_absent_deduction' => 'nullable|boolean',
        ]);

        $period = PayrollPeriod::findOrFail($data['payroll_period_id']);
        abort_if($period->status !== 'Draft', 422, 'Adjustments can only be synced to a Draft payroll cycle.');

        $applyOvertime = (bool) ($data['apply_overtime'] ?? false);
        $applyAbsent   = (bool) ($data['apply_absent_deduction'] ?? false);
        $overtimeRate  = (float) ($data['overtime_rate_lrd'] ?? 500);

        if (!$applyOvertime && !$applyAbsent) {
            return back()->with('warning', 'Select at least one adjustment option (Overtime Pay or Absenteeism Deduction).');
        }

        $summaries = ExternalAttendanceSummary::where('period_month', $data['period_month'])->get();
        $updatedRecords = 0;

        foreach ($summaries as $summary) {
            $record = PayrollRecord::where('payroll_period_id', $period->id)
                ->where('employee_id', $summary->employee_id)
                ->first();

            if (!$record) {
                continue;
            }

            $touched = false;

            if ($applyOvertime && (float) $summary->overtime_hours > 0 && $overtimeRate > 0) {
                $record->lineItems()
                    ->where('category', 'Overtime Pay')
                    ->where('description', 'like', "External Attendance Sync ({$data['period_month']})%")
                    ->delete();

                $amount = round((float) $summary->overtime_hours * $overtimeRate, 2);
                PayrollLineItem::create([
                    'payroll_record_id' => $record->id,
                    'type'              => 'allowance',
                    'category'          => 'Overtime Pay',
                    'description'       => "External Attendance Sync ({$data['period_month']}): {$summary->overtime_hours} hrs @ LRD {$overtimeRate}/hr",
                    'currency'          => 'LRD',
                    'amount'            => $amount,
                    'created_by'        => $request->user()->id,
                ]);
                $touched = true;
            }

            if ($applyAbsent && (int) $summary->days_absent > 0) {
                $record->lineItems()
                    ->where('category', 'Absenteeism Deduction')
                    ->where('description', 'like', "External Attendance Sync ({$data['period_month']})%")
                    ->delete();

                $dailyRate = $summary->working_days > 0
                    ? ((float) $record->basic_salary_lrd / $summary->working_days)
                    : 0;
                $dedAmount = round($dailyRate * (int) $summary->days_absent, 2);

                if ($dedAmount > 0) {
                    PayrollLineItem::create([
                        'payroll_record_id' => $record->id,
                        'type'              => 'deduction',
                        'category'          => 'Absenteeism Deduction',
                        'description'       => "External Attendance Sync ({$data['period_month']}): {$summary->days_absent} absent day(s)",
                        'currency'          => 'LRD',
                        'amount'            => $dedAmount,
                        'created_by'        => $request->user()->id,
                    ]);
                    $touched = true;
                }
            }

            if ($touched) {
                $summary->update(['payroll_period_id' => $period->id]);
                $record->recalculateTotals();
                $updatedRecords++;
            }
        }

        AuditService::log(
            'external_attendance_synced_payroll',
            PayrollPeriod::class,
            $period->id,
            [],
            ['period_month' => $data['period_month'], 'updated_records' => $updatedRecords],
            "Synced external attendance ({$data['period_month']}) adjustments into Draft payroll '{$period->name}' ({$updatedRecords} staff updated)"
        );

        return redirect()->route('payroll.show', $period->id)
            ->with('success', "Applied external attendance adjustments ({$data['period_month']}) to {$updatedRecords} personnel record(s) in '{$period->name}'.");
    }
}
