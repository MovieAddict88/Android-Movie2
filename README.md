# Car Rental Management System

A production-ready fullstack car rental management system featuring a public website, an admin panel, and an Android application.

## Tech Stack
- **Backend & Web:** Pure PHP, MySQL, Tailwind CSS
- **Mobile:** Android (Kotlin), Retrofit, Coroutines
- **Database:** MySQL

## Features
- **Public Website:**
  - Modern, fluid, and responsive design (Mobile, Tablet, Desktop, Smart TV).
  - Car browsing and searching.
  - User registration and login.
  - Real-time booking requests.
  - Contact form.
- **Admin Panel:**
  - Dashboard with key statistics.
  - Manage car fleet (Add, Edit, Delete).
  - Manage bookings (Confirm/Cancel).
  - Manage customer data.
- **Android Application:**
  - Clean Kotlin implementation.
  - Integration with the backend API.
  - Browse cars and view details.
  - User authentication and booking tracking.

## Installation

### Backend & Database
1. Import `database/schema.sql` into your MySQL server.
2. Configure your database credentials in `backend/includes/config.php`.
3. Host the `backend/` directory on a PHP-compatible web server (e.g., Apache, Nginx).
4. Default Admin Login:
   - **Email:** admin@example.com
   - **Password:** admin123

### Android Application
1. Open the `android/` directory in Android Studio.
2. Update the `BASE_URL` in `RetrofitClient` (to be implemented/configured) to point to your hosted backend API.
3. Build and run on an emulator or physical device.

## Project Structure
- `backend/`: PHP source code for the website, admin panel, and API.
- `android/`: Kotlin source code and layouts for the Android application.
- `database/`: SQL schema for the system.
