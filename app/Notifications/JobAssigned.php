<?php

namespace App\Notifications;

use App\Models\Location;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class JobAssigned extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * The location/job being assigned.
     */
    public Location $location;

    /**
     * Create a new notification instance.
     */
    public function __construct(Location $location)
    {
        $this->location = $location;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['fcm'];
    }

    /**
     * Get the FCM notification data.
     */
    public function toFcm(object $notifiable): array
    {
        return [
            'notification' => [
                'title' => 'New Job Assigned',
                'body' => "You have a new job: {$this->location->name}",
            ],
            'data' => [
                'type' => 'job_assigned',
                'location_id' => (string) $this->location->id,
                'location_name' => $this->location->name,
                'address' => $this->location->address,
                'latitude' => (string) $this->location->latitude,
                'longitude' => (string) $this->location->longitude,
                'radius' => (string) $this->location->radius,
                'scheduled_date' => $this->location->scheduled_date?->toDateString(),
                'click_action' => 'OPEN_LOCATION_DETAILS',
            ],
        ];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New Job Assigned')
            ->line("You have been assigned a new job: {$this->location->name}")
            ->line("Address: {$this->location->address}")
            ->action('View Job', url('/'))
            ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'location_id' => $this->location->id,
            'location_name' => $this->location->name,
            'address' => $this->location->address,
        ];
    }
}
