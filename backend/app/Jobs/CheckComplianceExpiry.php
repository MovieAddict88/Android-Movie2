<?php

namespace App\Jobs;

use App\Models\EmployeeDocument;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Stancl\Tenancy\Facades\Tenancy;

class CheckComplianceExpiry implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job across all tenants.
     */
    public function handle(): void
    {
        \App\Models\Tenant::all()->each(function ($tenant) {
            $tenant->run(function () use ($tenant) {
                $this->processTenantCompliance($tenant);
            });
        });
    }

    protected function processTenantCompliance($tenant): void
    {
        $upcomingExpirations = EmployeeDocument::query()
            ->whereNotNull('expiry_date')
            ->whereBetween('expiry_date', [
                Carbon::now(),
                Carbon::now()->addDays(30)
            ])
            ->with(['user'])
            ->get();

        foreach ($upcomingExpirations as $document) {
            // AI-driven optimal time detection (simulated)
            $optimalTime = $this->getAIOptimalReminderTime($document->user);

            if (Carbon::now()->isSameAs('H:i', $optimalTime)) {
                $this->notifyStakeholders($document, $tenant);
            }
        }

        $this->updateAllComplianceScores();
    }

    /**
     * Smart Reminder Scheduling:
     * AI detects employee engagement patterns (simulated logic).
     */
    protected function getAIOptimalReminderTime($user): string
    {
        // In production, this would call a model that analyzes 'last_login' or 'activity_logs'
        // For this implementation, we return the user's preferred time or a peak engagement window.
        $engagementWindow = $user->settings['peak_engagement_hour'] ?? '14:00';

        Log::info("AI determined optimal reminder time for User {$user->id} is {$engagementWindow}");

        return $engagementWindow;
    }

    protected function notifyStakeholders($document, $tenant): void
    {
        $webhookUrl = $tenant->settings['webhook_url'] ?? null;

        if ($webhookUrl) {
            Http::post($webhookUrl, [
                'text' => "Compliance Alert: {$document->user->name}'s {$document->type} is expiring on {$document->expiry_date->format('Y-m-d')}."
            ]);
        }
    }

    protected function updateAllComplianceScores(): void
    {
        $users = User::all();
        foreach ($users as $user) {
            $score = 100;
            $expiringCount = EmployeeDocument::where('user_id', $user->id)
                ->where('expiry_date', '<=', Carbon::now()->addDays(30))
                ->count();
            $expiredCount = EmployeeDocument::where('user_id', $user->id)
                ->where('expiry_date', '<', Carbon::now())
                ->count();

            $score -= ($expiringCount * 10);
            $score -= ($expiredCount * 30);
            $user->update(['compliance_score' => max(0, $score)]);
        }
    }
}
