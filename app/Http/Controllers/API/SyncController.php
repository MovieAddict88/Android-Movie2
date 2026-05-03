<?php

namespace App\Http\Controllers\API;

use App\Enums\JobStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\JobLogUploadRequest;
use App\Models\JobLog;
use App\Models\Location;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class SyncController extends Controller
{
    /**
     * Upload completed job log.
     */
    public function uploadJobLog(JobLogUploadRequest $request): JsonResponse
    {
        $user = $request->user();

        $location = Location::where('company_id', $user->company_id)
            ->where('id', $request->location_id)
            ->first();

        if (!$location) {
            return response()->json([
                'success' => false,
                'message' => 'Location not found or not assigned to you',
            ], 404);
        }

        $photoPath = null;
        if ($request->photo_base64) {
            $photoPath = $this->savePhoto($request->photo_base64, $user->company_id);
        }

        try {
            $jobLog = JobLog::create([
                'company_id' => $user->company_id,
                'location_id' => $request->location_id,
                'worker_id' => $user->id,
                'status' => JobStatus::from($request->status),
                'started_at' => $request->started_at,
                'completed_at' => $request->completed_at,
                'latitude_start' => $request->latitude_start,
                'longitude_start' => $request->longitude_start,
                'latitude_end' => $request->latitude_end,
                'longitude_end' => $request->longitude_end,
                'notes' => $request->notes,
                'photo_path' => $photoPath,
                'sync_id' => $request->sync_id,
                'metadata' => $request->metadata,
            ]);

            // Update location status if completed
            if ($request->status === 'completed') {
                $location->update(['status' => JobStatus::COMPLETED]);
            }

            $wasAtLocation = $location->isWithinGeofence(
                $request->latitude_end ?? 0,
                $request->longitude_end ?? 0
            );

            return response()->json([
                'success' => true,
                'message' => 'Job log uploaded successfully',
                'job_log' => [
                    'id' => $jobLog->id,
                    'sync_id' => $jobLog->sync_id,
                    'status' => $jobLog->status->value,
                    'started_at' => $jobLog->started_at?->toIso8601String(),
                    'completed_at' => $jobLog->completed_at?->toIso8601String(),
                    'photo_url' => $jobLog->photo_url,
                ],
                'location_verified' => $wasAtLocation,
                'verification_distance_meters' => $wasAtLocation ? 0 : $this->calculateDistance(
                    $location->latitude,
                    $location->longitude,
                    $request->latitude_end ?? 0,
                    $request->longitude_end ?? 0
                ),
            ], 201);
        } catch (\Exception $e) {
            Log::error('Job log upload failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to save job log',
            ], 500);
        }
    }

    /**
     * Get sync data - get latest changes since last sync.
     */
    public function sync(Request $request): JsonResponse
    {
        $request->validate([
            'last_sync_at' => ['nullable', 'date'],
        ]);

        try {
            $user = $request->user();
            $lastSync = $request->last_sync_at ? now()->parse($request->last_sync_at) : null;
            $syncWindow = $lastSync ?? now()->subDays(30);

            // Get locations
            $locations = Location::with('company')
                ->where('company_id', $user->company_id)
                ->where('assigned_to', $user->id)
                ->where('updated_at', '>', $syncWindow)
                ->get()
                ->map(function ($location) {
                    return [
                        'id' => $location->id,
                        'name' => $location->name,
                        'address' => $location->address,
                        'latitude' => (float) $location->latitude,
                        'longitude' => (float) $location->longitude,
                        'radius' => $location->radius,
                        'status' => $location->status->value,
                        'status_label' => $location->status->label(),
                        'notes' => $location->notes,
                        'scheduled_date' => $location->scheduled_date?->toDateString(),
                        'scheduled_time_start' => $location->scheduled_time_start,
                        'scheduled_time_end' => $location->scheduled_time_end,
                        'google_maps_url' => $location->google_maps_url,
                        'created_at' => $location->created_at->toIso8601String(),
                        'updated_at' => $location->updated_at->toIso8601String(),
                    ];
                });

            // Get job logs
            $jobLogs = JobLog::where('company_id', $user->company_id)
                ->where('worker_id', $user->id)
                ->where('updated_at', '>', $syncWindow)
                ->get()
                ->map(function ($jobLog) {
                    return [
                        'id' => $jobLog->id,
                        'location_id' => $jobLog->location_id,
                        'sync_id' => $jobLog->sync_id,
                        'status' => $jobLog->status->value,
                        'status_label' => $jobLog->status->label(),
                        'started_at' => $jobLog->started_at?->toIso8601String(),
                        'completed_at' => $jobLog->completed_at?->toIso8601String(),
                        'notes' => $jobLog->notes,
                        'photo_url' => $jobLog->photo_url,
                        'was_at_location' => $jobLog->wasCompletedAtLocation(),
                        'created_at' => $jobLog->created_at->toIso8601String(),
                        'updated_at' => $jobLog->updated_at->toIso8601String(),
                    ];
                });

            return response()->json([
                'success' => true,
                'sync_at' => now()->toIso8601String(),
                'locations_count' => $locations->count(),
                'job_logs_count' => $jobLogs->count(),
                'locations' => $locations,
                'job_logs' => $jobLogs,
            ]);
        } catch (\Exception $e) {
            Log::error('Sync failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Sync failed',
            ], 500);
        }
    }

    /**
     * Get job logs for the authenticated worker.
     */
    public function jobLogs(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            $query = JobLog::where('company_id', $user->company_id)
                ->where('worker_id', $user->id)
                ->with(['location:id,name,address']);

            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            if ($request->has('from_date')) {
                $query->whereDate('completed_at', '>=', $request->from_date);
            }

            if ($request->has('to_date')) {
                $query->whereDate('completed_at', '<=', $request->to_date);
            }

            $perPage = min($request->integer('per_page', 20), 100);
            $jobLogsPaginated = $query->orderBy('completed_at', 'desc')->paginate($perPage);

            $jobLogs = $jobLogsPaginated->map(function ($jobLog) {
                return [
                    'id' => $jobLog->id,
                    'location_id' => $jobLog->location_id,
                    'location_name' => $jobLog->location?->name,
                    'location_address' => $jobLog->location?->address,
                    'sync_id' => $jobLog->sync_id,
                    'status' => $jobLog->status->value,
                    'status_label' => $jobLog->status->label(),
                    'started_at' => $jobLog->started_at?->toIso8601String(),
                    'completed_at' => $jobLog->completed_at?->toIso8601String(),
                    'duration_minutes' => $jobLog->duration_in_minutes,
                    'notes' => $jobLog->notes,
                    'photo_url' => $jobLog->photo_url,
                    'signature_url' => $jobLog->signature_url,
                    'was_at_location' => $jobLog->wasCompletedAtLocation(),
                ];
            });

            return response()->json([
                'success' => true,
                'job_logs' => $jobLogs->items(),
                'pagination' => [
                    'current_page' => $jobLogsPaginated->currentPage(),
                    'last_page' => $jobLogsPaginated->lastPage(),
                    'per_page' => $jobLogsPaginated->perPage(),
                    'total' => $jobLogsPaginated->total(),
                    'has_more' => $jobLogsPaginated->hasMorePages(),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch job logs: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch job logs',
            ], 500);
        }
    }

    /**
     * Save base64 encoded photo.
     */
    private function savePhoto(string $base64Data, int $companyId): ?string
    {
        try {
            // Remove data URI prefix if present
            if (str_contains($base64Data, ',')) {
                $base64Data = substr($base64Data, strpos($base64Data, ',') + 1);
            }

            $imageData = base64_decode($base64Data);
            if ($imageData === false) {
                return null;
            }

            // Detect image type from magic bytes
            $finfo = new \finfo(FILEINFO_MIME_TYPE);
            $mimeType = $finfo->buffer($imageData);
            
            $extension = match ($mimeType) {
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
                'image/gif' => 'gif',
                'image/webp' => 'webp',
                default => 'jpg',
            };

            $filename = Str::uuid() . '.' . $extension;
            $path = JobLog::PHOTO_PATH . '/' . $companyId;

            Storage::disk('public')->put($path . '/' . $filename, $imageData);

            return $path . '/' . $filename;
        } catch (\Exception $e) {
            Log::error('Photo save failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Calculate distance between two coordinates.
     */
    private function calculateDistance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371000; // meters

        $latDelta = deg2rad($lat2 - $lat1);
        $lngDelta = deg2rad($lng2 - $lng1);

        $a = sin($latDelta / 2) * sin($latDelta / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($lngDelta / 2) * sin($lngDelta / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}