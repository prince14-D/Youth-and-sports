<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemNotification extends Model
{
    use HasFactory;

    protected $table = 'system_notifications';

    protected $fillable = [
        'user_id',
        'title',
        'message',
        'icon',
        'badge_color',
        'link_url',
        'is_read',
    ];

    protected $casts = [
        'is_read' => 'boolean',
    ];

    public static function notify($title, $message, $icon = 'bi-bell', $badge_color = 'primary', $link_url = null, $user_id = null)
    {
        return self::create([
            'user_id'     => $user_id,
            'title'       => $title,
            'message'     => $message,
            'icon'        => $icon,
            'badge_color' => $badge_color,
            'link_url'    => $link_url,
            'is_read'     => false,
        ]);
    }
}
