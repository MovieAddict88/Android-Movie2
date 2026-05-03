# Field Service Automation System

A complete field service automation solution with an Android app for field workers and a Laravel admin panel for business owners.

## Project Structure

```
/home/engine/project/
├── android/                    # Android App (Kotlin + Jetpack Compose)
│   └── app/
│       └── src/main/
│           ├── java/com/fieldservice/agent/
│           │   ├── data/         # Room DB, DAOs, Entities, Repositories
│           │   ├── di/           # Hilt DI modules
│           │   ├── service/      # Geofencing, Automation, FCM
│           │   ├── ui/           # Compose UI screens
│           │   ├── util/         # Utilities
│           │   └── worker/       # WorkManager sync
│           └── res/              # Resources
│
├── app/                        # Laravel Admin Panel
│   ├── Console/Commands/        # Artisan commands
│   ├── Http/Controllers/       # API & Web controllers
│   ├── Livewire/                # Livewire components
│   ├── Models/                 # Eloquent models
│   ├── Notifications/          # Notification classes
│   ├── Services/               # Business services
│   └── Enums/                  # PHP enums
│
├── database/migrations/        # Laravel migrations
├── routes/                     # API & web routes
└── .env.example               # Environment template
```

## Part 1: Android App

### Features

- **GPS Geofencing**: Automatically detects when worker enters job site (100m radius)
- **Local Database (Room)**: Stores all job locations and logs offline
- **WorkManager Sync**: Syncs completed jobs to Laravel backend when internet available
- **CameraX Integration**: Capture timestamped photos of completed work
- **Firebase Cloud Messaging (FCM)**: Receives new job locations from admin panel
- **Automation Core (AccessibilityService)**: Simulates taps/fills forms in other apps
- **Biometric Authentication**: Worker confirms identity before completing job
- **Offline Mode**: Works completely without internet (syncs later)

### Requirements

- **Language**: Kotlin
- **Min SDK**: API 31 (Android 12)
- **Target SDK**: API 35 (Android 14)
- **Build System**: Gradle with Kotlin DSL

### Setup

1. Clone the repository and navigate to the android folder:
```bash
cd android
```

2. Copy the Google Services configuration file:
```bash
cp google-services.json.example app/google-services.json
```

3. Update `app/build.gradle.kts` with your API base URL:
```kotlin
defaultConfig {
    buildConfigField("String", "API_BASE_URL", "\"https://your-api-url.com/\"")
}
```

4. Build the project:
```bash
./gradlew assembleDebug
```

### Permissions Required

```xml
- ACCESS_FINE_LOCATION
- ACCESS_COARSE_LOCATION
- ACCESS_BACKGROUND_LOCATION
- CAMERA
- USE_BIOMETRIC
- INTERNET
- ACCESS_NETWORK_STATE
- POST_NOTIFICATIONS
```

## Part 2: Laravel Admin Panel

### Features

- **Company Management**: Multi-tenant support
- **User Management**: Add/remove workers, assign device tokens
- **Location Management**: CRUD for job sites with geocoding
- **Push Notifications**: Send job sites to workers via FCM
- **Job Log Viewer**: View completed jobs with photos
- **REST API**: Android app sync with Laravel Sanctum
- **Geocoding**: Address to lat/lng conversion
- **Dashboard Charts**: Daily completed jobs, worker performance
- **Export Reports**: CSV/PDF export

### Requirements

- **PHP**: 8.2+
- **Laravel**: 12.x
- **Database**: MySQL 8.0+ or PostgreSQL 14+
- **Node.js**: 18+ (for frontend assets)

### Setup

1. Install dependencies:
```bash
composer install
```

2. Copy environment file:
```bash
cp .env.example .env
```

3. Configure your database in `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=field_service
DB_USERNAME=root
DB_PASSWORD=
```

4. Configure Firebase in `.env`:
```env
FIREBASE_CREDENTIALS=/path/to/firebase-credentials.json
FCM_SERVER_KEY=your-fcm-server-key
```

5. Configure Google Maps (optional):
```env
GOOGLE_MAPS_API_KEY=your-google-maps-api-key
```

6. Run migrations:
```bash
php artisan migrate
```

7. Seed the database (optional):
```bash
php artisan db:seed
```

8. Generate app key:
```bash
php artisan key:generate
```

9. Start the development server:
```bash
php artisan serve
```

## API Endpoints

### Authentication
- `POST /api/login` - Worker login
- `POST /api/logout` - Worker logout
- `GET /api/me` - Get current user

### Locations
- `GET /api/locations` - Get worker's assigned locations
- `GET /api/locations/{id}` - Get location details
- `PATCH /api/locations/{id}/status` - Update location status

### Sync
- `GET /api/sync` - Sync latest changes
- `POST /api/job-logs` - Upload completed job
- `GET /api/job-logs` - Get worker's job logs

## Artisan Commands

### Push Notifications
```bash
php artisan push:send {location_id} --worker={worker_id}
```

## License

MIT License
