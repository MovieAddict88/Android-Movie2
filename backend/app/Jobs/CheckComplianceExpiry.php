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
        // Iterate through all tenants to run compliance checks in their databases
        \App\Models\Tenant::all()->each(function ($tenant) {
            $tenant->run(function () use ($tenant) {
                $this->processTenantCompliance($tenant);
            });
        });
    }

    /**
     * Process compliance for a specific tenant.
     */
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
            $this->notifyStakeholders($document, $tenant);
        }

        // Batch update risk scores for users with documents
        $this->updateAllComplianceScores();
    }

    /**
     * Notify manager via Tenant-specific Webhook.
     */
    protected function notifyStakeholders($document, $tenant): void
    {
        $webhookUrl = $tenant->settings['webhook_url'] ?? null;

        if ($webhookUrl) {
            Http::post($webhookUrl, [
                'text' => "Compliance Alert: {$document->user->name}'s {$document->type} is expiring on {$document->expiry_date->format('Y-m-d')}."
            ]);
        }

        Log::info("Compliance notification sent for tenant {$tenant->id}, document ID: {$document->id}");
    }

    /**
     * Re-calculate compliance risk scores for all users in the current tenant.
     */
    protected function updateAllComplianceScores(): void
    {
        $users = User::all();

        foreach ($users as $user) {
            $score = 100;

            // Optimization: Get counts in one pass if possible, or simple aggregate
            $expiringCount = EmployeeDocument::where('user_id', $user->id)
                ->where('expiry_date', '<=', Carbon::now()->addDays(30))
                ->count();

            $expiredCount = EmployeeDocument::where('user_id', $user->id)
                ->where('expiry_date', '<', Carbon::now())
                ->count();

            $score -= ($expiringCount * 10);
            $score -= ($expiredCount * 30);

            $user->update([
                'compliance_score' => max(0, $score)
            ]);
        }
    }
}
