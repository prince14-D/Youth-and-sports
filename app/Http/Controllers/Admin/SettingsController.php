<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
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
            'host'         => (string) (config('mail.mailers.smtp.host') ?: 'smtp.hostinger.com'),
            'port'         => (string) (config('mail.mailers.smtp.port') ?: '465'),
            'scheme'       => (string) (config('mail.mailers.smtp.scheme') ?: ''),
            'username'     => (string) (config('mail.mailers.smtp.username') ?: ''),
            'has_password' => !empty(config('mail.mailers.smtp.password')),
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

    public function updateMail(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'mail_host'         => 'required|string|max:160',
            'mail_port'         => 'required|integer|in:465,587,25,2525',
            'mail_scheme'       => 'nullable|in:smtps,smtp,null,',
            'mail_username'     => 'required|email|max:160',
            'mail_password'     => 'nullable|string|max:255',
            'mail_from_address' => 'required|email|max:160',
            'mail_from_name'    => 'required|string|max:160',
        ]);

        $envPath = base_path('.env');
        if (!is_file($envPath) || !is_writable($envPath)) {
            return back()->with('error', '.env file is not writable on this server. Please update MAIL_* values directly in Hostinger File Manager.');
        }

        $scheme = ($data['mail_scheme'] ?? '') === '' ? 'null' : $data['mail_scheme'];
        if ((int) $data['mail_port'] === 465 && $scheme === 'null') {
            $scheme = 'smtps';
        }

        $updates = [
            'MAIL_MAILER'       => 'smtp',
            'MAIL_SCHEME'       => $scheme,
            'MAIL_HOST'         => $data['mail_host'],
            'MAIL_PORT'         => (string) $data['mail_port'],
            'MAIL_USERNAME'     => $this->quoteEnvValue($data['mail_username']),
            'MAIL_FROM_ADDRESS' => $this->quoteEnvValue($data['mail_from_address']),
            'MAIL_FROM_NAME'    => $this->quoteEnvValue($data['mail_from_name']),
        ];

        if (!empty($data['mail_password'])) {
            $updates['MAIL_PASSWORD'] = $this->quoteEnvValue($data['mail_password']);
        }

        $envContent = file_get_contents($envPath);
        foreach ($updates as $key => $value) {
            $pattern = "/^{$key}=.*$/m";
            if (preg_match($pattern, $envContent)) {
                $envContent = preg_replace($pattern, "{$key}={$value}", $envContent, 1);
            } else {
                $envContent .= "\n{$key}={$value}";
            }
        }
        file_put_contents($envPath, $envContent);

        try {
            Artisan::call('config:clear');
        } catch (\Throwable $e) {
            // Non-blocking if config cache file isn't present
        }

        AuditService::log('smtp_updated', 'SystemSettings', 1, [], [
            'mailer' => 'smtp', 'host' => $data['mail_host'], 'port' => $data['mail_port'], 'username' => $data['mail_username'],
        ], "Switched email delivery to SMTP ({$data['mail_host']}:{$data['mail_port']})");

        return redirect()->route('settings.index')->with('success', "SMTP settings saved ({$data['mail_host']}:{$data['mail_port']}). Click 'Test Delivery' below to verify!");
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
                . "\nSMTP Host: " . config('mail.mailers.smtp.host') . ':' . config('mail.mailers.smtp.port')
                . "\nFrom: " . config('mail.from.address')
                . "\nTimestamp: " . now()->format('d M Y, h:i A'),
                function ($msg) use ($data) {
                    $msg->to($data['test_email'])
                        ->subject('MYS HRMIS — Hostinger SMTP Delivery Test');
                }
            );

            AuditService::log('email_tested', 'SystemSettings', 1, [], ['to' => $data['test_email'], 'mailer' => config('mail.default')], "Sent SMTP/Email test to {$data['test_email']}");

            return back()->with('success', "Test email sent successfully to {$data['test_email']} via SMTP (" . config('mail.mailers.smtp.host') . ')!');
        } catch (\Throwable $e) {
            return back()->with('error', 'SMTP delivery failed: ' . $e->getMessage());
        }
    }

    private function quoteEnvValue(string $value): string
    {
        $clean = str_replace(["\r", "\n"], '', $value);
        $escaped = str_replace(['\\', '"', '$'], ['\\\\', '\\"', '\\$'], $clean);
        return '"' . $escaped . '"';
    }
}
