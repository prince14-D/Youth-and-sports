<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = [
            'ministry_name'      => config('app.name', 'Ministry of Youth & Sports'),
            'republic_label'     => 'Republic of Liberia',
            'work_start_time'    => '08:30',
            'work_end_time'      => '17:00',
            'standard_hours'     => '8.0',
            'retirement_age'     => '60',
            'currency_primary'   => 'LRD',
            'currency_secondary' => 'USD',
            'audit_logging'      => true,
        ];

        $mailConfig = [
            'mailer'       => (string) config('mail.default'),
            'host'         => (string) config('mail.mailers.smtp.host'),
            'port'         => (string) config('mail.mailers.smtp.port'),
            'scheme'       => (string) (config('mail.mailers.smtp.scheme') ?: 'tls / auto'),
            'username'     => (string) (config('mail.mailers.smtp.username') ?: 'Not set'),
            'from_address' => (string) config('mail.from.address'),
            'from_name'    => (string) config('mail.from.name'),
        ];

        return view('settings.index', compact('settings', 'mailConfig'));
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'work_start_time' => 'required|string',
            'work_end_time'   => 'required|string',
            'standard_hours'  => 'required|numeric',
            'retirement_age'  => 'required|integer',
        ]);

        AuditService::log('updated', 'SystemSettings', 1, [], $data, 'Updated Ministry System Configuration Settings');

        return redirect()->route('settings.index')->with('success', 'Ministry System Settings updated successfully!');
    }

    public function sendTestEmail(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'test_email' => 'required|email',
        ]);

        try {
            Mail::raw(
                "This is an official email connectivity test from the Ministry of Youth & Sports HRMIS.\n\nActive Mailer: "
                . config('mail.default')
                . "\nFrom: " . config('mail.from.address')
                . "\nTimestamp: " . now()->format('d M Y, h:i A'),
                function ($msg) use ($data) {
                    $msg->to($data['test_email'])
                        ->subject('MYS HRMIS — Email / SMTP Delivery Test');
                }
            );

            AuditService::log('email_tested', 'SystemSettings', 1, [], ['to' => $data['test_email'], 'mailer' => config('mail.default')], "Sent SMTP/Email test to {$data['test_email']}");

            return back()->with('success', "Test email sent successfully to {$data['test_email']} via [" . config('mail.default') . '] mailer!');
        } catch (\Throwable $e) {
            return back()->with('error', 'Email test failed (' . config('mail.default') . '): ' . $e->getMessage());
        }
    }
}
