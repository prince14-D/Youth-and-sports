<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditService
{
    public static function log(
        string $action,
        ?string $modelType = null,
        ?int $modelId = null,
        array $oldValues = [],
        array $newValues = [],
        ?string $description = null
    ): void {
        $user = Auth::user();

        AuditLog::create([
            'user_id'     => $user?->id,
            'user_name'   => $user?->name,
            'action'      => $action,
            'model_type'  => $modelType,
            'model_id'    => $modelId,
            'old_values'  => !empty($oldValues) ? $oldValues : null,
            'new_values'  => !empty($newValues) ? $newValues : null,
            'description' => $description,
            'ip_address'  => Request::ip(),
            'user_agent'  => Request::userAgent(),
            'url'         => Request::fullUrl(),
            'method'      => Request::method(),
        ]);
    }
}
