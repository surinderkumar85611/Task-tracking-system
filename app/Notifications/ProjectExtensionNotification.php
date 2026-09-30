<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class ProjectExtensionNotification extends Notification
{
    public function __construct(
        public string $type,      // requested | approved | rejected
        public string $message,
        public int $projectId
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'type'       => $this->type,
            'message'    => $this->message,
            'project_id' => $this->projectId,
        ];
    }
}