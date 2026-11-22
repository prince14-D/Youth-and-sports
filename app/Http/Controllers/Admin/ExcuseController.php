<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\ExcuseApplication;
use App\Services\AuditService;
use App\Services\UserNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExcuseController extends Controller
{
    private function isSelfServiceOnly($user): bool
    {
        return $user->hasAnyRole(['Employee', 'Contractor']) && !$user->can('view excuses');
    }

    public function index(Request $request): View
    {
        abort_unless(
            $request->user()->can('submit excuse') || $request->user()->can('view excuses'),
            403
        );

        $isSelfOnly = $this->isSelfServiceOnly($request->user());
        $query = ExcuseApplication::with(['employee.department'])->latest();

        if ($isSelfOnly) {
            $query->where('employee_id', $request->user()->employee_id);
        } elseif ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $excuses = $query->paginate(15)->withQueryString();

        $baseSummary = ExcuseApplication::query();
        if ($isSelfOnly) {
            $baseSummary->where('employee_id', $request->user()->employee_id);
        }

        $summary = [
            'pending'             => (clone $baseSummary)->where('status', 'Pending')->count(),
            'approved_this_month' => (clone $baseSummary)->where('status', 'Approved')->whereMonth('reviewed_at', now()->month)->whereYear('reviewed_at', now()->year)->count(),
            'today'               => (clone $baseSummary)->whereDate('excuse_date', today())->count(),
        ];

        return view('excuses.index', compact('excuses', 'summary'));
    }

    public function create(Request $request): View
    {
        $isSelfOnly = $this->isSelfServiceOnly($request->user());
        $employees = $isSelfOnly
            ? Employee::whereKey($request->user()->employee_id)->get()
            : Employee::active()->orderBy('first_name')->get();

        return view('excuses.create', compact('employees', 'isSelfOnly'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'excuse_date' => 'required|date|before_or_equal:today',
            'excuse_type' => 'required|string|max:50',
            'reason'      => 'required|string|max:1000',
            'attachment'  => 'nullable|file|max:5120|mimes:pdf,doc,docx,jpg,jpeg,png',
        ]);

        if ($this->isSelfServiceOnly($request->user())) {
            abort_unless((int) $request->user()->employee_id === (int) $data['employee_id'], 403);
        }

        $existing = ExcuseApplication::where('employee_id', $data['employee_id'])
            ->whereDate('excuse_date', $data['excuse_date'])
            ->whereIn('status', ['Pending', 'Approved'])
            ->exists();

        if ($existing) {
            return back()->withInput()->with('error', 'An excuse is already recorded or awaiting review for that date.');
        }

        $path = $request->hasFile('attachment')
            ? $request->file('attachment')->store('employees/excuses', 'public')
            : null;

        $excuse = ExcuseApplication::create([
            'employee_id'     => $data['employee_id'],
            'attendance_id'   => null,
            'excuse_date'     => $data['excuse_date'],
            'excuse_type'     => $data['excuse_type'],
            'reason'          => $data['reason'],
            'attachment_path' => $path,
            'status'          => 'Pending',
            'submitted_by'    => $request->user()->id,
        ]);

        $employee = Employee::findOrFail($data['employee_id']);
        AuditService::log('created', ExcuseApplication::class, $excuse->id, [], $excuse->toArray(), "Submitted excuse for {$employee->full_name} on {$data['excuse_date']}");
        UserNotificationService::send($employee->user, 'excuse_submitted', 'Excuse submitted', 'Your excuse request was recorded and sent for HR review.', ['url' => route('dashboard')]);

        return redirect()->route('excuses.index')->with('success', 'Excuse recorded and sent for HR review.');
    }

    public function approve(Request $request, ExcuseApplication $excuse): RedirectResponse
    {
        $old = $excuse->toArray();
        $excuse->update([
            'status'           => 'Approved',
            'reviewed_by'      => $request->user()->id,
            'reviewed_at'      => now(),
            'rejection_reason' => null,
        ]);

        AuditService::log('updated', ExcuseApplication::class, $excuse->id, $old, $excuse->fresh()->toArray(), "Approved excuse for {$excuse->employee->full_name}");
        UserNotificationService::send($excuse->employee->user, 'excuse_approved', 'Excuse approved', "Your excuse for {$excuse->excuse_date->format('d M Y')} was approved.", ['url' => route('dashboard')]);
        return back()->with('success', 'Excuse approved successfully.');
    }

    public function reject(Request $request, ExcuseApplication $excuse): RedirectResponse
    {
        $data = $request->validate(['rejection_reason' => 'required|string|max:500']);
        $old = $excuse->toArray();
        $excuse->update([
            'status'           => 'Rejected',
            'reviewed_by'      => $request->user()->id,
            'reviewed_at'      => now(),
            'rejection_reason' => $data['rejection_reason'],
        ]);

        AuditService::log('updated', ExcuseApplication::class, $excuse->id, $old, $excuse->fresh()->toArray(), "Rejected excuse for {$excuse->employee->full_name}");
        UserNotificationService::send($excuse->employee->user, 'excuse_rejected', 'Excuse rejected', "Your excuse was rejected: {$excuse->rejection_reason}", ['url' => route('dashboard')]);
        return back()->with('success', 'Excuse rejected.');
    }
}
