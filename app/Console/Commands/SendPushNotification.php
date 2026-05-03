<?php

namespace App\Console\Commands;

use App\Models\DeviceToken;
use App\Models\Location;
use App\Models\User;
use App\Notifications\JobAssigned;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class SendPushNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'push:send 
                            {location_id : The ID of the location/job to send}
                            {--worker= : Specific worker ID (optional, sends to all if not set)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send push notification to workers about a new job assignment';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $locationId = $this->argument('location_id');
        $workerId = $this->option('worker');

        $location = Location::with('assignedWorker')->find($locationId);

        if (!$location) {
            $this->error("Location with ID {$locationId} not found.");
            return Command::FAILURE;
        }

        if ($workerId) {
            $worker = User::find($workerId);
            if (!$worker) {
                $this->error("Worker with ID {$workerId} not found.");
                return Command::FAILURE;
            }
            $this->sendToWorker($worker, $location);
        } else {
            $workers = $location->assignedWorker 
                ? collect([$location->assignedWorker]) 
                : User::where('company_id', $location->company_id)
                    ->workers()
                    ->active()
                    ->get();

            if ($workers->isEmpty()) {
                $this->warn('No active workers found to notify.');
                return Command::SUCCESS;
            }

            foreach ($workers as $worker) {
                $this->sendToWorker($worker, $location);
            }
        }

        $this->info("Push notifications sent successfully!");
        return Command::SUCCESS;
    }

    /**
     * Send notification to a specific worker.
     */
    private function sendToWorker(User $worker, Location $location): void
    {
        $this->info("Sending notification to: {$worker->name} ({$worker->email})");

        try {
            $worker->notify(new JobAssigned($location));
            $this->info("  ✓ Notification sent to {$worker->name}");
        } catch (\Exception $e) {
            $this->error("  ✗ Failed to send to {$worker->name}: {$e->getMessage()}");
        }
    }
}
