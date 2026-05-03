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
│   ├── Http/Requests/          # Form request validation
│   ├── Livewire/                # Livewire components
│   ├── Models/                 # Eloquent models
│   ├── Notifications/           # Notification classes
│   ├── Services/               # Business services
│   └── Enums/                  # PHP enums
│
├── database/migrations/        # Laravel migrations
├── routes/                     # API & web routes
└── .env.example               # Environment template
```

## Part 1: Android App

### Features

- **GPS Geofencing**: Automatically detects when worker enters job site (configurable radius)
- **Local Database (Room)**: Stores all job locations and logs offline with migration support
- **WorkManager Sync**: Syncs completed jobs to Laravel backend with retry and exponential backoff
- **CameraX Integration**: Capture timestamped photos of completed work
- **Firebase Cloud Messaging (FCM)**: Receives new job locations from admin panel
- **Automation Core (AccessibilityService)**: Simulates taps/fills forms in other apps
- **Biometric Authentication**: Worker confirms identity before completing job
- **Offline Mode**: Works completely without internet (syncs later)
- **Pull-to-Refresh**: Manual refresh for locations list
- **Notifications Screen**: View and manage all notifications
- **Summary Statistics**: Dashboard with pending/in-progress/completed counts

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
- **Push Notifications**: Send job sites to workers via FCM with retry
- **Job Log Viewer**: View completed jobs with photos
- **REST API**: Android app sync with Laravel Sanctum
- **Geocoding**: Address to lat/lng conversion
- **Dashboard Charts**: Daily completed jobs, worker performance
- **Export Reports**: CSV/PDF export
- **Form Request Validation**: Validated API inputs with proper error messages
- **Rate Limiting**: Login rate limiting (5 attempts/minute)
- **Pagination**: All list endpoints support pagination
- **Advanced Filtering**: Search, sort, and filter locations
- **Notification Tracking**: In-app notification storage and management

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
| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/login` | Worker login with rate limiting |
| POST | `/api/logout` | Worker logout (revokes token) |
| GET | `/api/me` | Get current user info |
| POST | `/api/device-token` | Update FCM device token |

### Locations
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/locations` | List locations with filtering, search, pagination |
| GET | `/api/locations/{id}` | Get location details with recent jobs |
| PATCH | `/api/locations/{id}/status` | Update location status |

### Sync
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/sync` | Sync latest changes (locations & job logs) |
| POST | `/api/job-logs` | Upload completed job with validation |
| GET | `/api/job-logs` | List job logs with pagination |

### Notifications
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/notifications` | List notifications with pagination |
| PATCH | `/api/notifications/{id}/read` | Mark notification as read |
| POST | `/api/notifications/mark-all-read` | Mark all as read |
| GET | `/api/notifications/unread-count` | Get unread count |
| DELETE | `/api/notifications/{id}` | Delete notification |

### Health Check
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/health` | API health status |

## API Response Format

All API responses follow this format:

```json
{
    "success": true,
    "message": "Operation successful",
    "data": { ... },
    "pagination": {
        "current_page": 1,
        "last_page": 5,
        "per_page": 20,
        "total": 100,
        "has_more": true
    }
}
```

## Error Response Format

```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "email": ["The email field is required."],
        "password": ["The password must be at least 6 characters."]
    }
}
```

## Artisan Commands

### Push Notifications
```bash
# Send to specific worker
php artisan push:send {location_id} --worker={worker_id}

# Send to company
php artisan push:send {location_id} --company={company_id}
```

### Cleanup
```bash
# Clean old notifications (older than 30 days)
php artisan notifications:cleanup
```

## Tech Stack Summary

### Android
- Kotlin + Jetpack Compose
- Hilt (Dependency Injection)
- Room (Local Database)
- WorkManager (Background Sync)
- Retrofit + OkHttp (Networking)
- CameraX (Camera)
- Firebase Cloud Messaging
- Google Play Services (Location)
- Material Design 3

### Laravel
- PHP 8.2+
- Laravel 12.x
- Laravel Sanctum (API Auth)
- Livewire (Admin Panel)
- MySQL/PostgreSQL
- Firebase Cloud Messaging
- DomPDF (Reports)
- Maatwebsite Excel (Export)

## License

MIT License