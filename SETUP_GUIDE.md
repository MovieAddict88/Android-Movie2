# Phone Rental Management System

A professional solution for managing rented Android devices.

## Components

1.  **Admin Panel (PHP/MySQL):**
    - Responsive dashboard for mobile, tablet, and TV.
    - Device management (Lock/Unlock).
    - Application visibility control (Hide/Show apps by package).
    - API for communication with Android devices with API Key security.

2.  **User App (Kotlin):**
    - To be installed on the rented phone.
    - Features a rental timer.
    - Remote lock/unlock and app management listener.
    - **Note:** To hide applications, the app must be set as **Device Owner**.
      ```bash
      adb shell dpm set-device-owner com.example.phonerental.user/.DeviceAdminReceiver
      ```

3.  **Admin App (Kotlin):**
    - Remote controller for the Admin Panel.
    - Manage devices and app visibility on the go.

## Installation

1.  Upload `admin-panel/` to your PHP server.
2.  Navigate to `install.php` to set up the database.
3.  Note the **API_KEY** generated in `includes/db.php`.
4.  Update the `baseUrl` and `apiKey` in both Kotlin apps:
    - `BeaconService.kt` in User App.
    - `MainActivity.kt` in Admin App.

## Development

- **Android Studio:** Recommended version Hedgehog or Panda.
- **PHP:** 7.4+ or 8.x.
- **MySQL:** 5.7+ or 8.x.
