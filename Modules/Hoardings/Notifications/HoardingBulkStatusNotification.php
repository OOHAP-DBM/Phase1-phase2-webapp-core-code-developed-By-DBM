<?php
// Modules/Hoardings/Notifications/HoardingBulkStatusNotification.php

namespace Modules\Hoardings\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class HoardingBulkStatusNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $hoardings;
    public $action;
    public $adminName;

    public function __construct(Collection $hoardings, string $action, string $adminName = 'Admin')
    {
        $this->hoardings = $hoardings;
        $this->action    = $action;
        $this->adminName = $adminName;
    }

    public function via($notifiable): array
    {
        // ✅ Sirf database - mail NotificationEmailService se jayegi
        return ['database'];
    }

    // ✅ toMail() hataya - service handle karegi

    public function toArray($notifiable): array
    {
        $count   = $this->hoardings->count();
        $action  = ucfirst($this->action);
        $ids     = $this->hoardings->pluck('id')->toArray();
        $baseUrl = config('app.url');

        return [
            'type'         => 'vendor_hoarding_bulk_' . $this->action,
            'title'        => $this->action === 'approved'
                ? 'Hoardings Approved'
                : 'Hoardings Rejected',
            'message'      => "{$count} " . Str::plural('hoarding', $count) . " {$action} by {$this->adminName}",
            'hoarding_ids' => $ids,
            'action'       => $this->action,
            'action_url'   => rtrim($baseUrl, '/') . route('vendor.hoardings.myHoardings', [], false),
            'count'        => $count,
        ];
    }

    public function toDatabase($notifiable): array
    {
        return $this->toArray($notifiable);
    }
}