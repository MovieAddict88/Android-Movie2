# Field Service Admin Panel

This is the self-hosted dashboard for business owners to manage field workers and job sites.

## Features
- Multi-tenant Company Management
- User/Worker Management
- Job Site (Location) Management with Geofencing support
- Push Notifications to workers via FCM
- Job Log Viewer

## Tech Stack
- Laravel 12
- Livewire 3
- TailwindCSS
- MySQL/PostgreSQL
- Firebase Messaging (FCM)

## Setup Instructions
1. Clone the repository.
2. Run `composer install`.
3. Copy `.env.example` to `.env` and configure your database and Firebase credentials.
4. Run `php artisan key:generate`.
5. Run `php artisan migrate`.
6. Run `npm install && npm run build`.
7. Start the server: `php artisan serve`.

## API Endpoints
- `POST /api/login`: Worker authentication
- `GET /api/locations`: Fetch assigned job locations
- `POST /api/job-logs`: Upload completed job log
- `GET /api/sync`: Sync changes
