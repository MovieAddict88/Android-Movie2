<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Location;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    /**
     * Get all locations assigned to the authenticated worker.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        
        $query = Location::where('company_id', $user->company_id)
            ->where('assigned_to', $user->id)
            ->with('company');

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('date')) {
            $query->whereDate('scheduled_date', $request->date);
        }

        if ($request->boolean('active_only')) {
            $query->active();
        }

        $locations = $query->orderBy('scheduled_date', 'asc')
            ->orderBy('scheduled_time_start', 'asc')
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
                    'status_label' => $location->status->label(),
                    'notes' => $location->notes,
                    'scheduled_date' => $location->scheduled_date?->toDateString(),
                    'scheduled_time_start' => $location->scheduled_time_start,
                    'scheduled_time_end' => $location->scheduled_time_end,
                    'google_maps_url' => $location->google_maps_url,
                    'company_name' => $location->company->name,
                ];
            });

        return response()->json([
            'success' => true,
            'locations' => $locations,
            'count' => $locations->count(),
        ]);
    }

    /**
     * Get a specific location.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $location = Location::where('company_id', $user->company_id)
            ->where('id', $id)
            ->with(['company', 'assignedWorker', 'jobLogs' => function ($query) {
                $query->latest()->limit(10);
            }])
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'location' => [
                'id' => $location->id,
                'name' => $location->name,
                'address' => $location->address,
                'latitude' => $location->latitude,
                'longitude' => $location->longitude,
                'radius' => $location->radius,
                'status' => $location->status->value,
                'status_label' => $location->status->label(),
                'notes' => $location->notes,
                'scheduled_date' => $location->scheduled_date?->toDateString(),
                'scheduled_time_start' => $location->scheduled_time_start,
                'scheduled_time_end' => $location->scheduled_time_end,
                'google_maps_url' => $location->google_maps_url,
                'company' => [
                    'id' => $location->company->id,
                    'name' => $location->company->name,
                ],
                'assigned_worker' => $location->assignedWorker ? [
                    'id' => $location->assignedWorker->id,
                    'name' => $location->assignedWorker->name,
                ] : null,
                'recent_jobs' => $location->jobLogs->map(function ($job) {
                    return [
                        'id' => $job->id,
                        'status' => $job->status->value,
                        'completed_at' => $job->completed_at?->toIso8601String(),
                        'notes' => $job->notes,
                    ];
                }),
            ],
        ]);
    }

    /**
     * Update location status.
     */
    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'status' => 'required|in:pending,assigned,in_progress,completed,cancelled,failed',
        ]);

        $user = $request->user();

        $location = Location::where('company_id', $user->company_id)
            ->where('id', $id)
            ->firstOrFail();

        $location->update(['status' => $request->status]);

        return response()->json([
            'success' => true,
            'location' => [
                'id' => $location->id,
                'status' => $location->status->value,
                'status_label' => $location->status->label(),
            ],
        ]);
    }
}
