<?php

namespace App\Http\Controllers\API;

use App\Enums\JobStatus;
use App\Http\Controllers\Controller;
use App\Models\JobLog;
use App\Models\Location;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SyncController extends Controller
{
    /**
     * Upload completed job log.
     */
    public function uploadJobLog(Request $request): JsonResponse
    {
        $request->validate([
            'location_id' => 'required|integer',
            'sync_id' => 'required|string|unique:job_logs,sync_id',
            'status' => 'required|in:completed,failed,cancelled',
            'started_at' => 'required|date',
            'completed_at' => 'required|date',
            'latitude_start' => 'nullable|numeric',
            'longitude_start' => 'nullable|numeric',
            'latitude_end' => 'nullable|numeric',
            'longitude_end' => 'nullable|numeric',
            'notes' => 'nullable|string',
            'photo_base64' => 'nullable|string',
            'metadata' => 'nullable|array',
        ]);

        $user = $request->user();

        $location = Location::where('company_id', $user->company_id)
            ->where('id', $request->location_id)
            ->first();

        if (!$location) {
            return response()->json([
                'success' => false,
                'message' => 'Location not found',
            ], 404);
        }

        $photoPath = null;
        if ($request->photo_base64) {
            $photoPath = $this->savePhoto($request->photo_base64, $user->company_id);
        }

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

        if ($request->status === 'completed') {
            $location->update(['status' => JobStatus::COMPLETED]);
        }

        return response()->json([
            'success' => true,
            'job_log' => [
                'id' => $jobLog->id,
                'sync_id' => $jobLog->sync_id,
                'status' => $jobLog->status->value,
                'completed_at' => $jobLog->completed_at?->toIso8601String(),
            ],
            'location_verified' => $location->isWithinGeofence(
                $request->latitude_end ?? 0,
                $request->longitude_end ?? 0
            ),
        ], 201);
    }

    /**
     * Get sync data - get latest changes since last sync.
     */
    public function sync(Request $request): JsonResponse
    {
        $request->validate([
            'last_sync_at' => 'nullable|date',
        ]);

        $user = $request->user();
        $lastSync = $request->last_sync_at ? now()->parse($request->last_sync_at) : null;

        $locations = Location::where('company_id', $user->company_id)
            ->where('assigned_to', $user->id)
            ->where('updated_at', '>', $lastSync ?? now()->subDays(30))
            ->get()
            ->map(function ($location) {
                return [
                    'id' => $location->id,
                    'name' => $location->name,
                    'address' => $location->address,
                    'latitude' => $location->latitude,
                    'longitude' => $location->longitude,
                    'radius' => $location->radius,
                    'status' => $location->status->value,
                    'notes' => $location->notes,
                    'scheduled_date' => $location->scheduled_date?->toDateString(),
                    'scheduled_time_start' => $location->scheduled_time_start,
                    'scheduled_time_end' => $location->scheduled_time_end,
                    'updated_at' => $location->updated_at->toIso8601String(),
                ];
            });

        $jobLogs = JobLog::where('company_id', $user->company_id)
            ->where('worker_id', $user->id)
            ->where('updated_at', '>', $lastSync ?? now()->subDays(30))
            ->get()
            ->map(function ($jobLog) {
                return [
                    'id' => $jobLog->id,
                    'location_id' => $jobLog->location_id,
                    'sync_id' => $jobLog->sync_id,
                    'status' => $jobLog->status->value,
                    'started_at' => $jobLog->started_at?->toIso8601String(),
                    'completed_at' => $jobLog->completed_at?->toIso8601String(),
                    'notes' => $jobLog->notes,
                    'photo_url' => $jobLog->photo_url,
                    'updated_at' => $jobLog->updated_at->toIso8601String(),
                ];
            });

        return response()->json([
            'success' => true,
            'sync_at' => now()->toIso8601String(),
            'locations' => $locations,
            'job_logs' => $jobLogs,
        ]);
    }

    /**
     * Get job logs for the authenticated worker.
     */
    public function jobLogs(Request $request): JsonResponse
    {
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

        $jobLogs = $query->orderBy('completed_at', 'desc')
            ->paginate($request->integer('per_page', 20))
            ->map(function ($jobLog) {
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
                'current_page' => $jobLogs->currentPage(),
                'last_page' => $jobLogs->lastPage(),
                'per_page' => $jobLogs->perPage(),
                'total' => $jobLog->total(),
            ],
        ]);
    }

    /**
     * Save base64 encoded photo.
     */
    private function savePhoto(string $base64Data, int $companyId): ?string
    {
        try {
            $imageData = base64_decode($base64Data);
            $extension = 'jpg';
            $filename = Str::uuid() . '.' . $extension;
            $path = JobLog::PHOTO_PATH . '/' . $companyId;

            Storage::disk('public')->put(
                $path . '/' . $filename,
                $imageData
            );

            return $path . '/' . $filename;
        } catch (\Exception $e) {
            return null;
        }
    }
}
