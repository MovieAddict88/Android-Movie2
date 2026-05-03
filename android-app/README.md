# Field Service Agent (Android)

Automation agent for field workers to track jobs, handle geofencing, and sync data.

## Features
- GPS Geofencing for job site detection
- Local Room Database for offline storage
- WorkManager for background data sync
- Biometric Authentication for job completion
- CameraX Integration (Placeholder logic)
- Accessibility Service for UI automation

## Tech Stack
- Kotlin
- Jetpack Compose
- Room DB
- WorkManager
- Firebase Cloud Messaging (FCM)
- Retrofit
- iText (PDF generation)

## Setup Instructions
1. Open the project in Android Studio.
2. Add your `google-services.json` to the `app/` directory.
3. Update the `BASE_URL` in your API service to point to your Laravel backend.
4. Build and run the app on an Android 12+ device.

## Permissions Required
- Fine/Coarse Location (and Background Location)
- Camera
- Biometric
- Post Notifications
- Accessibility Service (Must be enabled manually in settings)
