<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\SyncController;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/locations', [SyncController::class, 'getLocations']);
    Route::post('/job-logs', [SyncController::class, 'uploadJobLog']);
    Route::get('/sync', [SyncController::class, 'sync']);
});
