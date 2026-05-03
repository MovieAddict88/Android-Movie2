<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Kreait\Laravel\Firebase\Facades\Firebase;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification as FCMNotification;

class JobAssigned extends Notification
{
    use Queueable;

    protected $location;

    public function __construct($location)
    {
        $this->location = $location;
    }

    public function via($notifiable): array
    {
        return ['firebase'];
    }

    public function toFirebase($notifiable)
    {
        $deviceTokens = $notifiable->deviceTokens()->pluck('token')->toArray();

        if (empty($deviceTokens)) {
            return;
        }

        $message = CloudMessage::new()
            ->withNotification(FCMNotification::create(
                'New Job Assigned',
                "New job site: {$this->location->name}"
            ))
            ->withData([
                'type' => 'NEW_JOB',
                'location_id' => (string) $this->location->id,
                'name' => $this->location->name,
                'lat' => (string) $this->location->latitude,
                'lng' => (string) $this->location->longitude,
            ]);

        Firebase::messaging()->sendMulticast($message, $deviceTokens);
    }
}
