<?php

namespace App\Services;

use App\Mail\HrAlertEmail;
use App\Models\Employee;
use App\Models\Notification;
use App\Models\SystemNotification;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class HrAlertService
{
    public static function getRadarData(): array
    {
        $today = today();

        $activeContractors = Employee::with(['department', 'user'])
            ->active()
            ->where('employment_type', 'Contract')
            ->whereNotNull('contract_end_date')
            ->orderBy('contract_end_date')
            ->get();

        $expiredContracts = $activeContractors->filter(fn ($e) => $e->contract_end_date->isPast())->values();
        $urgent7d = $activeContractors->filter(fn ($e) => !$e->contract_end_date->isPast() && $today->diffInDays($e->contract_end_date) <= 7)->values();
        $warning30d = $activeContractors->filter(fn ($e) => !$e->contract_end_date->isPast() && $today->diffInDays($e->contract_end_date) > 7 && $today->diffInDays($e->contract_end_date) <= 30)->values();
        $notice60d = $activeContractors->filter(fn ($e) => !$e->contract_end_date->isPast() && $today->diffInDays($e->contract_end_date) > 30 && $today->diffInDays($e->contract_end_date) <= 60)->values();

        $retiringSoon = Employee::with(['department', 'user'])
            ->active()
            ->whereNotNull('date_of_birth')
            ->whereDate('date_of_birth', '<=', $today->copy()->subYears(58)->toDateString())
            ->orderBy('date_of_birth')
            ->get();

        $expiringDocuments = \App\Models\EmployeeDocument::with(['employee.department', 'employee.user'])
            ->whereNotNull('expiry_date')
            ->whereDate('expiry_date', '<=', $today->copy()->addDays(30)->toDateString())
            ->orderBy('expiry_date')
            ->get();

        return compact(
            'expiredContracts',
            'urgent7d',
            'warning30d',
            'notice60d',
            'retiringSoon',
            'expiringDocuments'
        );
    }

    public static function runAll(bool $sendEmails = true, bool $forceToday = false): array
    {
        $radar = self::getRadarData();
        $today = today();

        $stats = [
            'contracts_expired'  => $radar['expiredContracts']->count(),
            'contracts_7d'       => $radar['urgent7d']->count(),
            'contracts_30d'      => $radar['warning30d']->count(),
            'contracts_60d'      => $radar['notice60d']->count(),
            'retiring_soon'      => $radar['retiringSoon']->count(),
            'expiring_docs'      => $radar['expiringDocuments']->count(),
            'notifications_sent' => 0,
            'emails_sent'        => 0,
            'emails_failed'      => 0,
        ];

        $hrStakeholders = User::role([
            'Human Resource Director',
            'Assistant HR Director',
            'HR Officer',
            'HR Data Analyst',
        ])->where('is_active', true)->get();

        // 1. Contract Expiry Alerts (Expired, 7d, 30d, 60d)
        $contractBuckets = [
            ['items' => $radar['expiredContracts'], 'tier' => 'expired', 'label' => 'EXPIRED CONTRACT',   'color' => '#dc2626'],
            ['items' => $radar['urgent7d'],         'tier' => '7d',      'label' => '7-DAY URGENT',       'color' => '#ea580c'],
            ['items' => $radar['warning30d'],       'tier' => '30d',     'label' => '30-DAY WARNING',     'color' => '#d97706'],
            ['items' => $radar['notice60d'],        'tier' => '60d',     'label' => '60-DAY NOTICE',      'color' => '#2563eb'],
        ];

        foreach ($contractBuckets as $bucket) {
            foreach ($bucket['items'] as $contractor) {
                $daysLeft = (int) $today->diffInDays($contractor->contract_end_date, false);
                $statusText = $daysLeft < 0
                    ? "expired " . abs($daysLeft) . " day(s) ago on {$contractor->contract_end_date->format('d M Y')}"
                    : "expires in {$daysLeft} day(s) on {$contractor->contract_end_date->format('d M Y')}";

                $alertType = "contract_expiry_{$bucket['tier']}_{$contractor->id}";

                // Notify Contractor
                if ($contractor->user && self::shouldNotifyToday($contractor->user->id, $alertType, $forceToday)) {
                    UserNotificationService::send(
                        $contractor->user,
                        $alertType,
                        "{$bucket['label']}: Contract Expiry Reminder",
                        "Your Ministry contract ({$contractor->employee_no}) {$statusText}. Please submit a renewal request if continuing service.",
                        ['url' => route('contractor.dashboard')]
                    );
                    $stats['notifications_sent']++;

                    if ($sendEmails) {
                        self::tryEmail(
                            $contractor->official_email ?: $contractor->user->email,
                            new HrAlertEmail(
                                "Contract Expiry ({$bucket['label']})",
                                $contractor->full_name,
                                $bucket['label'],
                                $bucket['color'],
                                'Contract Renewal Reminder',
                                "Your Ministry of Youth & Sports contract {$statusText}. Please log in to your Contractor Portal to submit a renewal request or coordinate with HR.",
                                [
                                    'Contractor ID' => $contractor->employee_no,
                                    'Department'    => $contractor->department?->name ?? 'Ministry HQ',
                                    'End Date'      => $contractor->contract_end_date->format('d M Y'),
                                    'Status'        => $daysLeft < 0 ? 'Expired' : "{$daysLeft} day(s) remaining",
                                ],
                                route('contractor.dashboard'),
                                'Open Contractor Portal'
                            ),
                            $stats
                        );
                    }
                }

                // Notify HR Stakeholders (HR Director, Asst HR Director, HR Officers, HR Data Analyst)
                foreach ($hrStakeholders as $hrUser) {
                    $hrType = "hr_{$alertType}";
                    if (self::shouldNotifyToday($hrUser->id, $hrType, $forceToday)) {
                        UserNotificationService::send(
                            $hrUser,
                            $hrType,
                            "{$bucket['label']}: {$contractor->full_name}",
                            "Contractor {$contractor->full_name} ({$contractor->employee_no}) {$statusText}.",
                            ['url' => route('hr-alerts.index')]
                        );
                        $stats['notifications_sent']++;
                    }
                }
            }
        }

        // 2. Statutory Retirement Alerts (Age 58+ in Liberia)
        $hrLeadership = $hrStakeholders->filter(fn ($u) => $u->hasAnyRole(['Human Resource Director', 'Assistant HR Director', 'HR Officer']));
        foreach ($radar['retiringSoon'] as $staff) {
            $age = $staff->date_of_birth?->age ?? 58;
            $retireDate = $staff->date_of_birth?->copy()->addYears(60);
            $alertType = "retirement_alert_{$staff->id}";

            foreach ($hrLeadership as $leader) {
                if (self::shouldNotifyToday($leader->id, $alertType, $forceToday)) {
                    UserNotificationService::send(
                        $leader,
                        $alertType,
                        "Statutory Retirement Notice: {$staff->full_name}",
                        "{$staff->full_name} ({$staff->employee_no}, age {$age}) reaches Liberia's 60-year statutory retirement on " . ($retireDate?->format('d M Y') ?? 'soon') . '.',
                        ['url' => route('hr-alerts.index')]
                    );
                    $stats['notifications_sent']++;
                }
            }
        }

        // 3. Expiring Document Alerts (<= 30 days)
        foreach ($radar['expiringDocuments'] as $doc) {
            if (!$doc->employee) {
                continue;
            }
            $docType = "doc_expiry_{$doc->id}";
            if ($doc->employee->user && self::shouldNotifyToday($doc->employee->user->id, $docType, $forceToday)) {
                UserNotificationService::send(
                    $doc->employee->user,
                    $docType,
                    'Document Expiry Reminder',
                    "Your document '{$doc->title}' expires on {$doc->expiry_date->format('d M Y')}. Please upload an updated copy.",
                    ['url' => route('employee.profile')]
                );
                $stats['notifications_sent']++;
            }
        }

        // 4. Consolidated SystemNotification banner if any alerts triggered
        $totalFlagged = $stats['contracts_expired'] + $stats['contracts_7d'] + $stats['contracts_30d'] + $stats['contracts_60d'] + $stats['retiring_soon'];
        if ($totalFlagged > 0) {
            SystemNotification::notify(
                'HR Expiry & Retirement Radar Update',
                "{$stats['contracts_expired']} expired & " . ($stats['contracts_7d'] + $stats['contracts_30d'] + $stats['contracts_60d']) . " expiring contracts, {$stats['retiring_soon']} retirement notice(s) active.",
                'bi-exclamation-triangle-fill',
                ($stats['contracts_expired'] + $stats['contracts_7d']) > 0 ? 'warning' : 'primary',
                route('hr-alerts.index')
            );
        }

        return $stats;
    }

    private static function shouldNotifyToday(int $userId, string $type, bool $forceToday): bool
    {
        if ($forceToday) {
            return true;
        }
        return !Notification::where('user_id', $userId)
            ->where('type', $type)
            ->whereDate('created_at', today())
            ->exists();
    }

    private static function tryEmail(?string $email, HrAlertEmail $mailable, array &$stats): void
    {
        if (!$email) {
            return;
        }
        try {
            Mail::to($email)->send($mailable);
            $stats['emails_sent']++;
        } catch (\Throwable $e) {
            $stats['emails_failed']++;
            Log::warning('HR alert email failed.', ['email' => $email, 'error' => $e->getMessage()]);
        }
    }
}
