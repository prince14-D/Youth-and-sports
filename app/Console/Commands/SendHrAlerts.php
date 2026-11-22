<?php

namespace App\Console\Commands;

use App\Services\HrAlertService;
use Illuminate\Console\Command;

class SendHrAlerts extends Command
{
    protected $signature = 'hr:send-alerts {--no-email : Send in-app alerts only without emailing} {--force : Re-send alerts even if already sent today}';
    protected $description = 'Dispatch tiered contract expiry (60/30/7d & expired), statutory retirement, and document expiry alerts.';

    public function handle(): int
    {
        $sendEmails = !$this->option('no-email');
        $force = (bool) $this->option('force');

        $stats = HrAlertService::runAll($sendEmails, $force);

        $this->info('MYS HRMIS Automated Alerts Processed:');
        $this->table(
            ['Category', 'Count'],
            [
                ['Expired Contracts', $stats['contracts_expired']],
                ['Expiring <= 7 Days (Urgent)', $stats['contracts_7d']],
                ['Expiring 8-30 Days (Warning)', $stats['contracts_30d']],
                ['Expiring 31-60 Days (Notice)', $stats['contracts_60d']],
                ['Statutory Retirement (Age 58+)', $stats['retiring_soon']],
                ['Expiring Documents (<= 30d)', $stats['expiring_docs']],
                ['In-App Notifications Sent', $stats['notifications_sent']],
                ['Emails Sent', $stats['emails_sent']],
                ['Emails Failed', $stats['emails_failed']],
            ]
        );

        return self::SUCCESS;
    }
}
