<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemNotification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function markAllAsRead()
    {
        SystemNotification::query()->update(['is_read' => true]);
        return response()->json(['success' => true]);
    }

    public function markAsRead($id)
    {
        $notification = SystemNotification::findOrFail($id);
        $notification->update(['is_read' => true]);

        if ($notification->link_url) {
            return redirect($notification->link_url);
        }

        return redirect()->back()->with('success', 'Notification marked as read.');
    }
}
