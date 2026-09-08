<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class ProfileUpdatedNotification extends Notification
{
    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'type' => 'profile_update',
            'title' => 'Profile Updated',
            'body' => 'Your profile details have been updated successfully.',
            'user_id' => $notifiable->id,
            'created_at' => now(),
        ];
    }
}
