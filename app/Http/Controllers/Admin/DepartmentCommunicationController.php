<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\DepartmentCommunicationEmail;
use App\Models\Department;
use App\Models\DepartmentCommunication;
use App\Models\DepartmentCommunicationRecipient;
use App\Models\Employee;
use App\Services\AuditService;
use App\Services\UserNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\View\View;

class DepartmentCommunicationController extends Controller
{
    public function index(): View
    {
        $communications = DepartmentCommunication::with(['fromDepartment', 'toDepartment', 'sender', 'recipients.employee'])->latest()->paginate(15);
        $departments = Department::active()->orderBy('name')->get();
        return view('communications.departments.index', compact('communications', 'departments'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'from_department_id' => 'required|exists:departments,id', 'to_department_id' => 'required|different:from_department_id|exists:departments,id',
            'subject' => 'required|string|max:180', 'message' => 'required|string|max:10000',
            'communication_type' => 'required|in:Request,Notice,Approval,Document Submission',
            'attachment' => 'nullable|file|max:10240|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png',
        ]);
        $communication = DepartmentCommunication::create([...$data, 'attachment_path' => $request->hasFile('attachment') ? $request->file('attachment')->store('department-communications', 'private') : null, 'sent_by' => $request->user()->id, 'reference_no' => 'DC-' . now()->format('Ymd') . '-' . Str::upper(Str::random(6))]);
        $from = Department::findOrFail($data['from_department_id']);
        $recipients = Employee::active()->where('department_id', $data['to_department_id'])->with('user')->get();
        $emails = [];
        foreach ($recipients as $employee) {
            $email = $employee->official_email ?: $employee->personal_email;
            if ($email) { $emails[] = $email; DepartmentCommunicationRecipient::create(['department_communication_id' => $communication->id, 'employee_id' => $employee->id, 'email' => $email, 'sent_at' => now()]); }
            UserNotificationService::send($employee->user, 'department_communication', 'New department communication', "{$from->name} sent request {$communication->reference_no}: {$communication->subject}", ['url' => route('department-communications.index')]);
        }
        foreach (array_unique($emails) as $email) Mail::to($email)->queue(new DepartmentCommunicationEmail($communication->reference_no, $from->name, $communication->subject, $communication->message, route('department-communications.index')));
        AuditService::log('created', DepartmentCommunication::class, $communication->id, [], $communication->toArray(), "Department communication {$communication->reference_no} sent");
        return back()->with('success', "Communication {$communication->reference_no} sent to {$recipients->count()} recipient(s).");
    }

    public function respond(Request $request, DepartmentCommunication $communication): RedirectResponse
    {
        $data = $request->validate(['response' => 'required|string|max:10000', 'response_attachment' => 'nullable|file|max:10240|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png']);
        $communication->update(['status' => 'Responded', 'response' => $data['response'], 'response_attachment_path' => $request->hasFile('response_attachment') ? $request->file('response_attachment')->store('department-communications', 'private') : null, 'responded_by' => $request->user()->id, 'responded_at' => now()]);
        return back()->with('success', 'Department response recorded.');
    }
}
