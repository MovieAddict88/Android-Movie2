# Deployment Guide: cPanel Multi-Tenant Laravel

## Prerequisites
- PHP 8.2+
- MySQL/MariaDB
- Redis (for Reverb/Queues)
- SSH Access

## Step-by-Step Installation

1. **Upload Codebase:**
   - Upload the Laravel project to a directory above `public_html`.
   - Symlink `public` to `public_html/schoolconnect`.

2. **Database Setup:**
   - Create a 'landlord' database (e.g., `schoolconnect_main`).
   - Configure `.env` with landlord credentials.

3. **Wildcard Subdomain:**
   - In cPanel, create a wildcard subdomain `*.schoolconnect.com` pointing to the public folder.

4. **Queue Management:**
   - Since cPanel lacks Supervisor, use a Cron job to run the queue worker:
     `* * * * * php /path/to/artisan queue:work --stop-when-empty >> /dev/null 2>&1`

5. **Multi-Tenancy Configuration:**
   - Ensure the database user has `CREATE DATABASE` permissions if automated school onboarding is enabled.
