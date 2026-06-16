<?php

namespace App\Services;

use App\Models\Notification;

class NotificationService
{
    public static function create($userId, $workspaceId, $type, $title, $message, $data = null)
    {
        return Notification::create([
            'user_id' => $userId,
            'workspace_id' => $workspaceId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
           'data' => $data,
            'is_read' => 0,
        ]);
    }
}