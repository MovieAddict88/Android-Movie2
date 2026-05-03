<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\LocationController;
use App\Http\Controllers\API\SyncController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public routes (no auth required)
Route::post('/login', [AuthController::class, 'login']);

// Protected routes (Sanctum auth required)
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/device-token', [AuthController::class, 'updateDeviceToken']);

    // Locations
    Route::get('/locations', [LocationController::class, 'index']);
    Route::get('/locations/{id}', [LocationController::class, 'show']);
    Route::patch('/locations/{id}/status', [LocationController::class, 'updateStatus']);

    // Sync & Job Logs
    Route::get('/sync', [SyncController::class, 'sync']);
    Route::post('/job-logs', [SyncController::class, 'uploadJobLog']);
    Route::get('/job-logs', [SyncController::class, 'jobLogs']);
});
