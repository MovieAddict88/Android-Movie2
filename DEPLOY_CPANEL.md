# DEPLOYMENT GUIDE (cPanel & Web Hosting)

Follow these steps to deploy the ChoreQuest Laravel backend and Flutter Web panel.

## 1. Backend Deployment (Laravel)
1. **Upload Files:** Upload the `backend/` directory content to `public_html` or a subdomain folder.
2. **Setup Database:** Create a MySQL database in cPanel and update `.env`.
3. **Environment Variables:**
   ```env
   APP_ENV=production
   GEMINI_API_KEY=your_key_here
   STABILITY_API_KEY=your_key_here
   QUEUE_CONNECTION=redis
   ```
4. **Migrations:** Run `php artisan migrate --force` via Terminal or Cron.
5. **Cron Jobs:** Add the following cron job in cPanel (running every minute):
   ```bash
   * * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
   ```
6. **Queue Worker:** Use Laravel Horizon or a background process to run `php artisan queue:work`.

## 2. Realtime Features (Reverb)
- Ensure Port `8080` (or your configured Reverb port) is open in the cPanel firewall.
- Start Reverb: `php artisan reverb:start`.

## 3. Frontend Deployment (Flutter Web)
1. **Build:** Run `flutter build web --release`.
2. **Upload:** Upload the `build/web/` directory to your web root.
3. **PWA:** Ensure `manifest.json` and service workers are correctly mapped for offline support.

## 4. Maintenance Tasks
- **AI Cache Cleanup:** The scheduled task `php artisan ai:cleanup` (configured in `routes/console.php`) deletes old coloring pages every 30 days.
- **Weekly Reports:** Ensure `mail` driver is configured for weekly parent emails.
