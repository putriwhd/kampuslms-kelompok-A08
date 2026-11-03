<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class AdminActivityNotification extends Notification
{
    public function __construct(
        public string $title,
        public string $message,
        public string $url
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => $this->title,
            'message' => $this->message,
            'url' => $this->url,
        ];
    }
}
