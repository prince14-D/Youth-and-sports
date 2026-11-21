<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use App\Mail\EmployeeUpdate;
use Illuminate\Support\Facades\Mail;

class UserNotificationService
{
    public static function send(?User $user, string $type, string $title, string $message, array $data = []): void
    {
        if (!$user) {
            return;
        }

        Notification::create([
            'user_id' => $user->id,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'data' => $data,
        ]);

        if ($user->email) {
            try {
                Mail::to($user->email)->queue(new EmployeeUpdate($title, $message, $data['url'] ?? null));
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Notification email failed to send.', [
                    'user_id' => $user->id,
                    'email'   => $user->email,
                    'error'   => $e->getMessage(),
                ]);
            }
        }
    }
}
