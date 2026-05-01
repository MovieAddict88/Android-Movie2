# OnboardAI Agency Deployment Guide

This guide details how to deploy and white-label OnboardAI for your HR SaaS clients.

## 1. Multi-Tenancy Architecture
OnboardAI uses **stancl/tenancy** for Laravel. Each client (tenant) has their own database for data isolation and compliance.

### Database Setup
1. Create a "landlord" database (e.g., `onboardai_main`).
2. Grant the database user permissions to create new databases.
3. In `config/tenancy.php`, ensure the `central_domains` are correctly mapped.

## 2. AI Integration (Gemini 2.0 Flash)
The system uses Google Gemini for job parsing and document analysis.

### API Configuration
- **Master Key:** Set `GEMINI_API_KEY` in your central `.env`. This is used for global tasks.
- **Tenant Keys:** Clients can provide their own key in Tenant Settings to avoid shared usage limits.

## 3. cPanel Deployment Steps

### Requirements
- PHP 8.3+
- MySQL 8.0+
- Redis (for Queues)
- SSH Access

### Installation
1. **Clone & Install Dependencies:**
   ```bash
   composer install --optimize-autoloader --no-dev
   ```
2. **Environment Setup:**
   Copy `.env.example` to `.env` and configure:
   - `DB_HOST`, `DB_USERNAME`, `DB_PASSWORD`
   - `REVERB_APP_ID`, `REVERB_APP_KEY`, `REVERB_APP_SECRET`
   - `GEMINI_API_KEY`
   - `FILESYSTEM_DISK=s3` (Recommended for document storage)

3. **Cron Jobs:**
   Add the following to cPanel Cron Jobs:
   ```cron
   * * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
   ```
   *This handles the `CheckComplianceExpiry` job across all tenants.*

4. **Queue Worker:**
   Use a Process Manager (like Supervisor) or a cPanel Terminal to run:
   ```bash
   php artisan queue:work --queue=default,documents
   ```

5. **Horizon Dashboard:**
   Monitor queue health at `yourdomain.com/horizon`. Ensure you've restricted access to Super Admins.

## 4. White-Labeling & Themes
- **Dynamic Branding:** The Flutter app fetches primary/secondary colors from the Tenant API. Ensure the backend settings return valid hex codes.
- **Flutter Implementation:** Widgets use `Theme.of(context).primaryColor` to ensure they adapt to the specific client's brand without code changes.
- **Assets:** Update `assets/logo.png` and run `flutter pub run flutter_launcher_icons`.

## 5. Compliance Features
- **Tenant-Aware Jobs:** The compliance check job iterates through all registered tenants to calculate risk scores and send notifications.
- **Risk Scoring:** AI automatically lowers scores for employees with expiring documents.
- **Auto-Fill:** When employees upload IDs, the system uses OCR + AI to populate their profile, reducing data entry errors.

## 6. Offline Support
The Flutter app includes basic offline handling for task completion and video playback. Offline actions are queued and synchronized once a connection is re-established.
