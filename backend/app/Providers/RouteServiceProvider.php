<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        RateLimiter::for('document-uploads', function (Request $request) {
            // Rate limit 10 per minute per tenant
            $tenantId = $request->header('X-Tenant-Id') ?? $request->user()?->tenant_id ?? 'default';
            return Limit::perMinute(10)->by($tenantId);
        });
    }
}
