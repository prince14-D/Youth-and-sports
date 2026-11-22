<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\DocumentRoutingLog;
use App\Models\MinistryDocument;
use App\Models\User;
use App\Services\AuditService;
use App\Services\UserNotificationService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class DocumentWorkflowController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $userDeptId = $user->employee?->department_id;
        $isExecutive = (new MinistryDocument())->hasExecutiveOversight($user);

        $tab = $request->query('tab', 'inbox');
        $statusFilter = $request->query('status');
        $search = trim((string) $request->query('search', ''));

        $baseQuery = MinistryDocument::with([
            'originDepartment',
            'currentDepartment',
            'destinationDepartment',
            'destinationUser',
            'sender',
            'currentHandler',
            'latestFeedbackUser',
            'routingLogs',
        ])->visibleTo($user);

        if ($statusFilter) {
            $baseQuery->where('status', $statusFilter);
        }
        if ($search !== '') {
            $baseQuery->where(function ($q) use ($search) {
                $q->where('tracking_number', 'like', "%{$search}%")
                  ->orWhere('subject', 'like', "%{$search}%")
                  ->orWhere('document_type', 'like', "%{$search}%");
            });
        }

        if ($tab === 'inbox') {
            $query = (clone $baseQuery)
                ->where(function ($q) use ($user, $userDeptId) {
                    $q->where(function ($du) use ($user) {
                        $du->where('destination_user_id', $user->id)
                           ->whereNotIn('status', ['Returned', 'Completed', 'Rejected']);
                    });
                    if ($userDeptId) {
                        $q->orWhere(function ($cd) use ($userDeptId) {
                            $cd->where('current_department_id', $userDeptId)
                               ->whereNotIn('status', ['Completed', 'Rejected']);
                        });
                    }
                });
        } elseif ($tab === 'reverted') {
            $query = (clone $baseQuery)->where('status', 'Returned');
        } elseif ($tab === 'outbox') {
            $query = (clone $baseQuery)->where(function ($q) use ($user, $userDeptId) {
                $q->where('sender_id', $user->id);
                if ($userDeptId) {
                    $q->orWhere('origin_department_id', $userDeptId);
                }
            });
        } else {
            $query = clone $baseQuery;
        }

        $documents = $query->latest('updated_at')->paginate(15)->withQueryString();

        $counts = [
            'inbox' => MinistryDocument::visibleTo($user)
                ->where(function ($q) use ($user, $userDeptId) {
                    $q->where(function ($du) use ($user) {
                        $du->where('destination_user_id', $user->id)
                           ->whereNotIn('status', ['Returned', 'Completed', 'Rejected']);
                    });
                    if ($userDeptId) {
                        $q->orWhere(function ($cd) use ($userDeptId) {
                            $cd->where('current_department_id', $userDeptId)
                               ->whereNotIn('status', ['Completed', 'Rejected']);
                        });
                    }
                })->count(),
            'reverted' => MinistryDocument::visibleTo($user)->where('status', 'Returned')->count(),
            'outbox'   => MinistryDocument::visibleTo($user)->where(function ($q) use ($user, $userDeptId) {
                $q->where('sender_id', $user->id);
                if ($userDeptId) {
                    $q->orWhere('origin_department_id', $userDeptId);
                }
            })->count(),
            'all'    => MinistryDocument::visibleTo($user)->count(),
            'urgent' => MinistryDocument::visibleTo($user)->where('priority', 'Urgent')->whereNotIn('status', ['Completed', 'Rejected'])->count(),
        ];

        $departments = Department::active()->orderBy('name')->get();
        $recipientUsers = User::with(['employee.department', 'roles'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
        $documentTypes = MinistryDocument::DOCUMENT_TYPES;
        $dmaDept = Department::where('code', 'DMA')->first();

        return view('documents.index', compact(
            'documents',
            'counts',
            'departments',
            'recipientUsers',
            'documentTypes',
            'dmaDept',
            'userDeptId',
            'isExecutive',
            'tab',
            'statusFilter',
            'search'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validate([
            'subject'                   => 'required|string|max:200',
            'document_type'             => 'required|string|max:80',
            'priority'                  => 'required|in:Normal,High,Urgent',
            'confidentiality'           => 'required|in:Restricted,Confidential,Internal',
            'origin_department_id'      => 'nullable|exists:departments,id',
            'destination_department_id' => 'required|exists:departments,id',
            'destination_user_id'       => 'nullable|exists:users,id',
            'marked_department_ids'     => 'nullable|array',
            'marked_department_ids.*'   => 'exists:departments,id',
            'amount_lrd'                => 'nullable|numeric|min:0',
            'amount_usd'                => 'nullable|numeric|min:0',
            'summary'                   => 'required|string|max:4000',
            'attachment'                => 'nullable|file|max:15360|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png',
        ]);

        $originDeptId = ($data['origin_department_id'] ?? null)
            ?: ($user->employee?->department_id ?: Department::where('code', 'HR')->value('id') ?: Department::value('id'));

        $destDeptId = (int) $data['destination_department_id'];
        $destUserId = !empty($data['destination_user_id']) ? (int) $data['destination_user_id'] : null;
        $destUser = $destUserId ? User::with('employee')->find($destUserId) : null;

        $marked = array_map('intval', $data['marked_department_ids'] ?? []);
        if ($destUser?->employee?->department_id) {
            $marked[] = (int) $destUser->employee->department_id;
        }
        $allowedIds = array_values(array_unique(array_merge([(int) $originDeptId, $destDeptId], $marked)));

        $attachmentPath = null;
        $attachmentName = null;
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $attachmentPath = $file->store('ministry/documents', 'public');
            $attachmentName = $file->getClientOriginalName();
        }

        $trackingNo = MinistryDocument::generateTrackingNumber();

        $doc = MinistryDocument::create([
            'tracking_number'               => $trackingNo,
            'subject'                       => $data['subject'],
            'document_type'                 => $data['document_type'],
            'priority'                      => $data['priority'],
            'confidentiality'               => $data['confidentiality'],
            'origin_department_id'          => $originDeptId,
            'current_department_id'         => $destDeptId,
            'destination_department_id'     => $destDeptId,
            'destination_user_id'           => $destUserId,
            'allowed_department_ids'        => $allowedIds,
            'sender_id'                     => $user->id,
            'amount_lrd'                    => ($data['amount_lrd'] ?? null) ?: null,
            'amount_usd'                    => ($data['amount_usd'] ?? null) ?: null,
            'summary'                       => $data['summary'],
            'attachment_path'               => $attachmentPath,
            'attachment_original_name'      => $attachmentName,
            'status'                        => 'In Transit',
            'received_at_current_office_at' => now(),
        ]);

        $originName = Department::find($originDeptId)?->name ?? 'Origin Office';
        $destName = Department::find($destDeptId)?->name ?? 'Destination Office';
        $destLabel = $destUser ? "{$destName} (Attn: {$destUser->name})" : $destName;

        $this->logMovement(
            $doc,
            (int) $originDeptId,
            $destDeptId,
            'dispatched',
            $user,
            "Dispatched from {$originName} to {$destLabel}." . ($attachmentName ? " Attached: {$attachmentName}" : ''),
            $destUserId,
            $attachmentPath,
            $attachmentName
        );

        $this->notifyDepartmentUsers(
            $destDeptId,
            'document_dispatched',
            "Incoming File ({$trackingNo}): {$doc->subject}",
            "{$originName} dispatched a {$doc->priority} {$doc->document_type} to {$destLabel}.",
            route('documents.show', $doc),
            $destUser
        );

        AuditService::log(
            'document_dispatched',
            MinistryDocument::class,
            $doc->id,
            [],
            ['tracking_number' => $trackingNo, 'to' => $destLabel, 'has_attachment' => (bool) $attachmentPath],
            "Dispatched document {$trackingNo} ({$doc->subject}) from {$originName} to {$destLabel}"
        );

        return redirect()->route('documents.show', $doc)
            ->with('success', "Document {$trackingNo} dispatched to {$destLabel}. Only marked offices, the destined person & executive leadership can access this file.");
    }

    public function track(Request $request): View
    {
        $query = trim((string) $request->query('q', ''));
        $document = null;
        $matches = collect();
        $accessDenied = false;
        $notFound = false;

        if ($query !== '') {
            $found = MinistryDocument::with([
                'originDepartment',
                'currentDepartment',
                'destinationDepartment',
                'destinationUser',
                'sender',
                'currentHandler',
                'latestFeedbackUser',
                'routingLogs.fromDepartment',
                'routingLogs.toDepartment',
                'routingLogs.targetUser',
            ])->whereRaw('LOWER(tracking_number) = ?', [strtolower($query)])->first();

            if ($found) {
                if (!$found->canUserView($request->user())) {
                    // Strictly block unmarked offices from seeing sender, marked offices, destination, or details!
                    $accessDenied = true;
                } else {
                    $document = $found;
                }
            } else {
                // Allow keyword search across documents visible to this user
                $matches = MinistryDocument::with(['originDepartment', 'currentDepartment', 'destinationDepartment', 'destinationUser', 'latestFeedbackUser'])
                    ->visibleTo($request->user())
                    ->where(function ($q) use ($query) {
                        $q->where('tracking_number', 'like', "%{$query}%")
                          ->orWhere('subject', 'like', "%{$query}%")
                          ->orWhere('document_type', 'like', "%{$query}%")
                          ->orWhereHas('destinationUser', fn ($uq) => $uq->where('name', 'like', "%{$query}%"));
                    })
                    ->latest('updated_at')
                    ->take(15)
                    ->get();

                if ($matches->count() === 1) {
                    $document = $matches->first()->load([
                        'originDepartment',
                        'currentDepartment',
                        'destinationDepartment',
                        'destinationUser',
                        'sender',
                        'currentHandler',
                        'latestFeedbackUser',
                        'routingLogs.fromDepartment',
                        'routingLogs.toDepartment',
                        'routingLogs.targetUser',
                    ]);
                    $matches = collect();
                } elseif ($matches->isEmpty()) {
                    $notFound = true;
                }
            }
        }

        return view('documents.track', compact('query', 'document', 'matches', 'accessDenied', 'notFound'));
    }

    public function show(Request $request, MinistryDocument $document): View
    {
        abort_unless(
            $document->canUserView($request->user()),
            403,
            'Access Restricted: Your office was not marked on this document by the sender.'
        );

        $document->load([
            'originDepartment',
            'currentDepartment',
            'destinationDepartment',
            'destinationUser',
            'sender',
            'currentHandler',
            'latestFeedbackUser',
            'routingLogs.fromDepartment',
            'routingLogs.toDepartment',
            'routingLogs.targetUser',
        ]);

        $markedDepartments = Department::whereIn('id', $document->effectiveAllowedDepartmentIds())->orderBy('name')->get();
        $departments = Department::active()->orderBy('name')->get();
        $recipientUsers = User::with(['employee.department', 'roles'])->where('is_active', true)->orderBy('name')->get();
        $canAct = $document->canUserActOnCurrentOffice($request->user());
        $canFeedback = $document->canUserGiveFeedback($request->user());
        $canResubmit = $document->canSenderResubmit($request->user());

        return view('documents.show', compact(
            'document',
            'markedDepartments',
            'departments',
            'recipientUsers',
            'canAct',
            'canFeedback',
            'canResubmit'
        ));
    }

    public function downloadAttachment(Request $request, MinistryDocument $document)
    {
        abort_unless($document->canUserView($request->user()), 403, 'Access Restricted.');
        abort_unless($document->attachment_path && Storage::disk('public')->exists($document->attachment_path), 404, 'Uploaded document not found.');

        $filename = $document->attachment_filename ?: basename($document->attachment_path);

        if ($request->boolean('preview')) {
            return Storage::disk('public')->response($document->attachment_path, $filename);
        }

        return Storage::disk('public')->download($document->attachment_path, $filename);
    }

    public function downloadLogAttachment(Request $request, MinistryDocument $document, DocumentRoutingLog $log)
    {
        abort_unless((int) $log->ministry_document_id === (int) $document->id, 404);
        abort_unless($document->canUserView($request->user()), 403, 'Access Restricted.');
        abort_unless($log->attachment_path && Storage::disk('public')->exists($log->attachment_path), 404, 'Attached file not found.');

        $filename = $log->attachment_original_name ?: basename($log->attachment_path);

        if ($request->boolean('preview')) {
            return Storage::disk('public')->response($log->attachment_path, $filename);
        }

        return Storage::disk('public')->download($log->attachment_path, $filename);
    }

    public function receive(Request $request, MinistryDocument $document): RedirectResponse
    {
        abort_unless($document->canUserActOnCurrentOffice($request->user()), 403, 'Only the destination office or destined person can acknowledge receipt.');

        $document->update([
            'status'                        => 'Received',
            'current_handler_id'            => $request->user()->id,
            'received_at_current_office_at' => now(),
        ]);

        $officeName = $document->currentDepartment?->name ?? 'Office';
        $note = trim((string) $request->input('minutes_note', '')) ?: "Receipt acknowledged at {$officeName} by {$request->user()->name}.";

        $this->logMovement($document, $document->current_department_id, $document->current_department_id, 'received', $request->user(), $note);

        AuditService::log(
            'document_received',
            MinistryDocument::class,
            $document->id,
            [],
            ['tracking_number' => $document->tracking_number, 'office' => $officeName],
            "Received document {$document->tracking_number} ({$document->subject}) at {$officeName}"
        );

        return back()->with('success', "Document {$document->tracking_number} marked as Received at {$officeName}.");
    }

    public function forward(Request $request, MinistryDocument $document): RedirectResponse
    {
        abort_unless($document->canUserActOnCurrentOffice($request->user()), 403, 'Only the current holding office or destined person can forward this document.');

        $data = $request->validate([
            'to_department_id'    => 'required|exists:departments,id',
            'to_user_id'          => 'nullable|exists:users,id',
            'minutes_note'        => 'required|string|max:2000',
            'response_attachment' => 'nullable|file|max:15360|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png',
        ]);

        $fromDeptId = $document->current_department_id;
        $toDeptId = (int) $data['to_department_id'];
        $toUserId = !empty($data['to_user_id']) ? (int) $data['to_user_id'] : null;
        $toUser = $toUserId ? User::with('employee')->find($toUserId) : null;

        $allowed = array_values(array_unique(array_merge(
            $document->effectiveAllowedDepartmentIds(),
            array_filter([$toDeptId, $toUser?->employee?->department_id])
        )));

        [$attPath, $attName] = $this->storeUploadedFile($request, 'response_attachment');

        $document->update([
            'current_department_id'         => $toDeptId,
            'destination_department_id'     => $toDeptId,
            'destination_user_id'           => $toUserId ?? $document->destination_user_id,
            'allowed_department_ids'        => $allowed,
            'current_handler_id'            => null,
            'latest_feedback'               => $data['minutes_note'],
            'latest_feedback_by'            => $request->user()->id,
            'latest_feedback_at'            => now(),
            'status'                        => 'In Transit',
            'received_at_current_office_at' => now(),
        ]);

        $toDept = Department::find($toDeptId);
        $targetLabel = $toUser ? "{$toDept?->name} (Attn: {$toUser->name})" : ($toDept?->name ?? 'Next Office');

        $this->logMovement($document, $fromDeptId, $toDeptId, 'forwarded', $request->user(), $data['minutes_note'], $toUserId, $attPath, $attName);

        $this->notifyDepartmentUsers(
            $toDeptId,
            'document_forwarded',
            "File Forwarded ({$document->tracking_number})",
            "{$request->user()->name} minuted & forwarded '{$document->subject}' to {$targetLabel}: {$data['minutes_note']}",
            route('documents.show', $document),
            $toUser
        );

        AuditService::log(
            'document_forwarded',
            MinistryDocument::class,
            $document->id,
            [],
            ['tracking_number' => $document->tracking_number, 'to' => $targetLabel],
            "Forwarded {$document->tracking_number} ({$document->subject}) to {$targetLabel}: {$data['minutes_note']}"
        );

        return back()->with('success', "Document {$document->tracking_number} minuted & forwarded to {$targetLabel}.");
    }

    public function action(Request $request, MinistryDocument $document): RedirectResponse
    {
        abort_unless($document->canUserActOnCurrentOffice($request->user()), 403, 'Only the destination office or destined person can act on this document.');

        $data = $request->validate([
            'decision'            => 'required|in:approve,revert,return,feedback,reject,complete',
            'minutes_note'        => 'required_if:decision,revert,return,reject,feedback|nullable|string|max:2500',
            'response_attachment' => 'nullable|file|max:15360|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png',
        ]);

        $decision = $data['decision'] === 'revert' ? 'return' : $data['decision'];
        $note = trim((string) ($data['minutes_note'] ?? ''));
        $currentDeptId = $document->current_department_id;
        [$attPath, $attName] = $this->storeUploadedFile($request, 'response_attachment');

        $feedbackUpdate = [];
        if ($note !== '') {
            $feedbackUpdate = [
                'latest_feedback'    => $note,
                'latest_feedback_by' => $request->user()->id,
                'latest_feedback_at' => now(),
            ];
        }

        if ($decision === 'approve') {
            $finalNote = $note !== '' ? $note : 'Approved by ' . $request->user()->name . ' (' . $request->user()->primary_role . ').';
            $document->update(array_merge([
                'status'             => 'Approved',
                'current_handler_id' => $request->user()->id,
                'latest_feedback'    => $finalNote,
                'latest_feedback_by' => $request->user()->id,
                'latest_feedback_at' => now(),
            ], $attPath ? ['attachment_path' => $attPath, 'attachment_original_name' => $attName] : []));

            $this->logMovement($document, $currentDeptId, $currentDeptId, 'approved', $request->user(), $finalNote, null, $attPath, $attName);
            UserNotificationService::send($document->sender, 'document_approved', "Document Approved ({$document->tracking_number})", "'{$document->subject}' was approved by {$request->user()->name}: {$finalNote}", ['url' => route('documents.show', $document)]);
            AuditService::log('document_approved', MinistryDocument::class, $document->id, [], ['tracking_number' => $document->tracking_number, 'feedback' => $finalNote], "Approved document {$document->tracking_number} ({$document->subject}): {$finalNote}");
            $msg = "Document {$document->tracking_number} approved and recorded with digital signature.";
        } elseif ($decision === 'return') {
            $originId = $document->origin_department_id;
            $document->update(array_merge([
                'current_department_id'         => $originId,
                'status'                        => 'Returned',
                'current_handler_id'            => null,
                'received_at_current_office_at' => now(),
            ], $feedbackUpdate));

            $this->logMovement($document, $currentDeptId, $originId, 'returned', $request->user(), $note, $document->sender_id, $attPath, $attName);
            UserNotificationService::send($document->sender, 'document_returned', "Document Reverted ({$document->tracking_number})", "'{$document->subject}' was reverted by {$request->user()->name} for revision: {$note}", ['url' => route('documents.show', $document)]);
            AuditService::log('document_reverted', MinistryDocument::class, $document->id, [], ['tracking_number' => $document->tracking_number, 'feedback' => $note], "Reverted document {$document->tracking_number} ({$document->subject}) to sender: {$note}");
            $msg = "Document {$document->tracking_number} reverted to sender with feedback.";
        } elseif ($decision === 'feedback') {
            $newStatus = $document->status === 'In Transit' ? 'Under Review' : $document->status;
            $document->update(array_merge([
                'status'             => $newStatus,
                'current_handler_id' => $request->user()->id,
            ], $feedbackUpdate));

            $this->logMovement($document, $currentDeptId, $currentDeptId, 'feedback', $request->user(), $note, null, $attPath, $attName);
            if ((int) $document->sender_id !== (int) $request->user()->id) {
                UserNotificationService::send($document->sender, 'document_feedback', "Feedback on {$document->tracking_number}", "{$request->user()->name} commented on '{$document->subject}': {$note}", ['url' => route('documents.show', $document)]);
            }
            AuditService::log('document_feedback', MinistryDocument::class, $document->id, [], ['tracking_number' => $document->tracking_number, 'feedback' => $note], "Recorded feedback on {$document->tracking_number} ({$document->subject}): {$note}");
            $msg = "Feedback recorded on document {$document->tracking_number}.";
        } elseif ($decision === 'reject') {
            $document->update(array_merge([
                'status'             => 'Rejected',
                'current_handler_id' => $request->user()->id,
                'completed_at'       => now(),
            ], $feedbackUpdate));

            $this->logMovement($document, $currentDeptId, $currentDeptId, 'rejected', $request->user(), $note, null, $attPath, $attName);
            UserNotificationService::send($document->sender, 'document_rejected', "Document Rejected ({$document->tracking_number})", "'{$document->subject}' was rejected by {$request->user()->name}: {$note}", ['url' => route('documents.show', $document)]);
            AuditService::log('document_rejected', MinistryDocument::class, $document->id, [], ['tracking_number' => $document->tracking_number, 'reason' => $note], "Rejected document {$document->tracking_number} ({$document->subject}): {$note}");
            $msg = "Document {$document->tracking_number} rejected.";
        } else {
            $finalNote = $note !== '' ? $note : 'Action completed & archived.';
            $document->update([
                'status'             => 'Completed',
                'current_handler_id' => $request->user()->id,
                'latest_feedback'    => $finalNote,
                'latest_feedback_by' => $request->user()->id,
                'latest_feedback_at' => now(),
                'completed_at'       => now(),
            ]);

            $this->logMovement($document, $currentDeptId, $currentDeptId, 'completed', $request->user(), $finalNote, null, $attPath, $attName);
            UserNotificationService::send($document->sender, 'document_completed', "Document Completed ({$document->tracking_number})", "'{$document->subject}' has been marked completed.", ['url' => route('documents.show', $document)]);
            AuditService::log('document_completed', MinistryDocument::class, $document->id, [], ['tracking_number' => $document->tracking_number], "Marked document {$document->tracking_number} ({$document->subject}) as Completed");
            $msg = "Document {$document->tracking_number} marked as Completed.";
        }

        return back()->with('success', $msg);
    }

    public function feedback(Request $request, MinistryDocument $document): RedirectResponse
    {
        abort_unless($document->canUserGiveFeedback($request->user()), 403, 'You are not authorized to add feedback on this document.');

        $data = $request->validate([
            'feedback_note'       => 'required|string|max:2500',
            'feedback_attachment' => 'nullable|file|max:15360|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png',
        ]);

        [$attPath, $attName] = $this->storeUploadedFile($request, 'feedback_attachment');
        $note = trim($data['feedback_note']);

        $document->update([
            'status'             => $document->status === 'In Transit' ? 'Under Review' : $document->status,
            'latest_feedback'    => $note,
            'latest_feedback_by' => $request->user()->id,
            'latest_feedback_at' => now(),
        ]);

        $this->logMovement(
            $document,
            $document->current_department_id,
            $document->current_department_id,
            'feedback',
            $request->user(),
            $note,
            null,
            $attPath,
            $attName
        );

        // Notify sender and destined person (excluding actor)
        $notifyUsers = collect([$document->sender, $document->destinationUser])
            ->filter(fn ($u) => $u && (int) $u->id !== (int) $request->user()->id)
            ->unique('id');
        foreach ($notifyUsers as $recipient) {
            UserNotificationService::send(
                $recipient,
                'document_feedback',
                "New Feedback ({$document->tracking_number})",
                "{$request->user()->name} ({$request->user()->primary_role}) left feedback on '{$document->subject}': {$note}",
                ['url' => route('documents.show', $document)]
            );
        }

        AuditService::log(
            'document_feedback',
            MinistryDocument::class,
            $document->id,
            [],
            ['tracking_number' => $document->tracking_number, 'feedback' => $note],
            "Feedback recorded on {$document->tracking_number} ({$document->subject}) by {$request->user()->name}: {$note}"
        );

        return back()->with('success', "Feedback recorded on {$document->tracking_number} and visible in the executive tracker.");
    }

    public function resubmit(Request $request, MinistryDocument $document): RedirectResponse
    {
        abort_unless($document->canSenderResubmit($request->user()), 403, 'Only the originating sender or office can revise and resubmit a reverted document.');

        $data = $request->validate([
            'minutes_note'       => 'required|string|max:2500',
            'revised_attachment' => 'nullable|file|max:15360|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png',
        ]);

        [$attPath, $attName] = $this->storeUploadedFile($request, 'revised_attachment');
        $nextRev = (int) $document->revision_count + 1;

        $update = [
            'current_department_id'         => $document->destination_department_id,
            'current_handler_id'            => null,
            'status'                        => 'In Transit',
            'revision_count'                => $nextRev,
            'latest_feedback'               => "Revision #{$nextRev} submitted: " . trim($data['minutes_note']),
            'latest_feedback_by'            => $request->user()->id,
            'latest_feedback_at'            => now(),
            'received_at_current_office_at' => now(),
        ];

        if ($attPath) {
            $update['attachment_path'] = $attPath;
            $update['attachment_original_name'] = $attName;
        }

        $document->update($update);

        $this->logMovement(
            $document,
            $document->origin_department_id,
            $document->destination_department_id,
            'resubmitted',
            $request->user(),
            "Revision #{$nextRev}: " . trim($data['minutes_note']) . ($attName ? " (Uploaded: {$attName})" : ''),
            $document->destination_user_id,
            $attPath,
            $attName
        );

        $this->notifyDepartmentUsers(
            $document->destination_department_id,
            'document_resubmitted',
            "Revised Document Resubmitted ({$document->tracking_number})",
            "{$request->user()->name} addressed feedback and resubmitted '{$document->subject}' (Revision #{$nextRev}).",
            route('documents.show', $document),
            $document->destinationUser
        );

        AuditService::log(
            'document_resubmitted',
            MinistryDocument::class,
            $document->id,
            [],
            ['tracking_number' => $document->tracking_number, 'revision' => $nextRev],
            "Resubmitted {$document->tracking_number} ({$document->subject}) — Revision #{$nextRev}: {$data['minutes_note']}"
        );

        return back()->with('success', "Revision #{$nextRev} of {$document->tracking_number} submitted back to {$document->destinationDepartment?->name}.");
    }

    public function movementSlipPdf(Request $request, MinistryDocument $document)
    {
        abort_unless($document->canUserView($request->user()), 403, 'Access Restricted.');
        $document->load([
            'originDepartment',
            'currentDepartment',
            'destinationDepartment',
            'destinationUser',
            'sender',
            'routingLogs.fromDepartment',
            'routingLogs.toDepartment',
            'routingLogs.targetUser',
        ]);
        $logoFile = public_path('images/logo.png');
        $logoDataUri = is_file($logoFile) ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoFile)) : null;

        return Pdf::loadView('documents.slip-pdf', compact('document', 'logoDataUri'))
            ->setPaper('a4', 'portrait')
            ->download("MYS-Movement-Docket-{$document->tracking_number}.pdf");
    }

    public function logMovement(
        MinistryDocument $doc,
        ?int $fromDeptId,
        ?int $toDeptId,
        string $action,
        User $actor,
        ?string $note,
        ?int $targetUserId = null,
        ?string $attachmentPath = null,
        ?string $attachmentOriginalName = null
    ): DocumentRoutingLog {
        $now = now();
        $sig = 'DOC-SIG-' . strtoupper(substr(hash('sha256', $doc->tracking_number . '|' . $action . '|' . $actor->id . '|' . $now->toIso8601String()), 0, 10));

        return DocumentRoutingLog::create([
            'ministry_document_id'     => $doc->id,
            'from_department_id'       => $fromDeptId,
            'to_department_id'         => $toDeptId,
            'target_user_id'           => $targetUserId,
            'action'                   => $action,
            'actor_id'                 => $actor->id,
            'actor_name'               => $actor->name,
            'actor_role'               => $actor->primary_role,
            'minutes_note'             => $note,
            'attachment_path'          => $attachmentPath,
            'attachment_original_name' => $attachmentOriginalName,
            'digital_signature'        => $sig,
            'acted_at'                 => $now,
        ]);
    }

    private function storeUploadedFile(Request $request, string $field): array
    {
        if (!$request->hasFile($field)) {
            return [null, null];
        }
        $file = $request->file($field);
        return [$file->store('ministry/documents', 'public'), $file->getClientOriginalName()];
    }

    private function notifyDepartmentUsers(int $departmentId, string $type, string $title, string $message, string $url, ?User $destinedUser = null): void
    {
        try {
            $dept = Department::find($departmentId);
            $users = User::where('is_active', true)
                ->whereHas('employee', fn ($q) => $q->where('department_id', $departmentId))
                ->get();

            if ($dept?->code === 'DMA') {
                $users = $users->merge(User::role('Deputy Minister for Administration')->where('is_active', true)->get());
            } elseif ($dept?->code === 'MINISTER') {
                $users = $users->merge(User::role('Minister')->where('is_active', true)->get());
            }

            if ($destinedUser && $destinedUser->is_active) {
                $users = $users->push($destinedUser);
            }

            foreach ($users->unique('id') as $recipient) {
                UserNotificationService::send($recipient, $type, $title, $message, ['url' => $url]);
            }
        } catch (\Throwable $e) {
            // Non-blocking notification failure
        }
    }
}
