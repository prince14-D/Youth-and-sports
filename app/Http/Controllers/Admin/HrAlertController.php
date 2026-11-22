<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AuditService;
use App\Services\HrAlertService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HrAlertController extends Controller
{
    public function index(): View
    {
        $radar = HrAlertService::getRadarData();
        return view('alerts.index', $radar);
    }

    public function runNow(Request $request): RedirectResponse
    {
        $sendEmails = $request->boolean('send_emails', false);
        $stats = HrAlertService::runAll($sendEmails, true);

        AuditService::log(
            'hr_alerts_dispatched',
            null,
            null,
            [],
            $stats,
            "Dispatched HR expiry & retirement alerts ({$stats['notifications_sent']} in-app, {$stats['emails_sent']} emails)"
        );

        $msg = "HR Alerts dispatched: {$stats['notifications_sent']} in-app notification(s) sent";
        if ($sendEmails) {
            $msg .= ", {$stats['emails_sent']} email(s) sent";
            if ($stats['emails_failed'] > 0) {
                $msg .= " ({$stats['emails_failed']} email(s) failed)";
            }
        }
        $msg .= '.';

        return back()->with($stats['emails_failed'] > 0 ? 'warning' : 'success', $msg);
    }
}
