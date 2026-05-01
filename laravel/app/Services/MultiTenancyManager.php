<?php

namespace App\Services;

use App\Models\Tenant;
use Stancl\Tenancy\Resolvers\DomainTenantResolver;

class MultiTenancyManager
{
    /**
     * Initialize tenancy for a specific domain.
     */
    public function initializeForDomain(string $domain)
    {
        $tenant = DomainTenantResolver::resolve($domain);
        tenancy()->initialize($tenant);
    }

    /**
     * Create a new tenant (school).
     */
    public function createSchool(string $id, string $name, string $domain)
    {
        $tenant = Tenant::create([
            'id' => $id,
            'name' => $name,
        ]);

        $tenant->domains()->create(['domain' => $domain]);

        return $tenant;
    }
}
