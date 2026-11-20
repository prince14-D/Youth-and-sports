<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\HrBroadcastEmail;
use App\Models\Department;
use App\Models\EmailCommunication;
use App\Models\Employee;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class EmailCommunicationController extends Controller
{
    public function index(): View
    {
        $communications = EmailCommunication::with(['sender', 'department'])->latest()->paginate(15);
        $departments = Department::active()->orderBy('name')->get();
        $employees = Employee::active()->where(function ($query) {
            $query->whereNotNull('official_email')->orWhereNotNull('personal_email');
        })->orderBy('first_name')->get(['id', 'employee_no', 'first_name', 'last_name', 'official_email', 'personal_email']);
        return view('communications.email.index', compact('communications', 'departments', 'employees'));
    }

    public function send(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'recipient_scope' => 'required|in:employee,department,all',
            'employee_id' => 'required_if:recipient_scope,employee|nullable|exists:employees,id',
            'department_id' => 'required_if:recipient_scope,department|nullable|exists:departments,id',
            'subject' => 'required|string|max:180',
            'body' => 'required|string|max:10000',
        ]);

        $employees = Employee::active()->where(function ($query) {
            $query->whereNotNull('official_email')->orWhereNotNull('personal_email');
        });
        if ($data['recipient_scope'] === 'employee') $employees->whereKey($data['employee_id']);
        if ($data['recipient_scope'] === 'department') $employees->where('department_id', $data['department_id']);

        $recipients = $employees->get()->map(fn ($employee) => $employee->official_email ?: $employee->personal_email)->filter()->unique()->values();
        if ($recipients->isEmpty()) return back()->withInput()->with('error', 'No active employees with email addresses match this selection.');

        $communication = EmailCommunication::create([
            'sent_by' => $request->user()->id,
            'recipient_scope' => $data['recipient_scope'],
            'department_id' => $data['department_id'] ?? null,
            'subject' => $data['subject'],
            'body' => $data['body'],
            'recipient_count' => $recipients->count(),
            'status' => 'Queued',
        ]);

        try {
            foreach ($recipients as $email) Mail::to($email)->queue(new HrBroadcastEmail($data['subject'], $data['body']));
            $communication->update(['status' => 'Sent', 'sent_at' => now()]);
        } catch (\Throwable $exception) {
            $communication->update(['status' => 'Failed', 'error_message' => $exception->getMessage()]);
            return back()->with('error', 'The email could not be sent. Check SMTP settings and try again.');
        }

        AuditService::log('created', EmailCommunication::class, $communication->id, [], $communication->toArray(), "HR email sent to {$recipients->count()} employee(s): {$data['subject']}");
        return back()->with('success', "Email sent to {$recipients->count()} employee(s).");
    }
}
