<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Location;
use App\Notifications\JobAssigned;

class SendPush extends Command
{
    protected $signature = 'push:job {user_id} {location_id}';
    protected $description = 'Send a job assignment push notification to a worker';

    public function handle()
    {
        $user = User::findOrFail($this->argument('user_id'));
        $location = Location::findOrFail($this->argument('location_id'));

        $user->notify(new JobAssigned($location));

        $this->info("Push notification sent to {$user->name} for {$location->name}");
    }
}
