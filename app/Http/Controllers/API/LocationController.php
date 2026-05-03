<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateLocationStatusRequest;
use App\Models\Location;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LocationController extends Controller
{
    /**
     * Get all locations assigned to the authenticated worker.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            $query = Location::with('company')
                ->where('company_id', $user->company_id)
                ->where('assigned_to', $user->id);

            // Filter by status
            if ($request->has('status')) {
                $statuses = explode(',', $request->status);
                $query->whereIn('status', $statuses);
            }

            // Filter by date
            if ($request->has('date')) {
                $query->whereDate('scheduled_date', $request->date);
            }

            // Filter by date range
            if ($request->has('from_date') && $request->has('to_date')) {
                $query->whereBetween('scheduled_date', [$request->from_date, $request->to_date]);
            }

            // Active only filter
            if ($request->boolean('active_only')) {
                $query->active();
            }

            // Search by name or address
            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('address', 'like', "%{$search}%");
                });
            }

            // Sorting
            $sortBy = $request->get('sort_by', 'scheduled_date');
            $sortOrder = $request->get('sort_order', 'asc');
            $allowedSorts = ['scheduled_date', 'scheduled_time_start', 'name', 'status', 'created_at'];
            
            if (in_array($sortBy, $allowedSorts)) {
                $query->orderBy($sortBy, $sortOrder === 'desc' ? 'desc' : 'asc');
            } else {
                $query->orderBy('scheduled_date', 'asc')
                      ->orderBy('scheduled_time_start', 'asc');
            }

            // Pagination
            $perPage = min($request->integer('per_page', 50), 100);
            $locationsPaginated = $query->paginate($perPage);

            $locations = $locationsPaginated->map(function ($location) {
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
                    'company_name' => $location->company?->name,
                    'distance_meters' => null, // Can be calculated client-side
                ];
            });

            return response()->json([
                'success' => true,
                'locations' => $locations->items(),
                'pagination' => [
                    'current_page' => $locationsPaginated->currentPage(),
                    'last_page' => $locationsPaginated->lastPage(),
                    'per_page' => $locationsPaginated->perPage(),
                    'total' => $locationsPaginated->total(),
                    'has_more' => $locationsPaginated->hasMorePages(),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch locations: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch locations',
            ], 500);
        }
    }

    /**
     * Get a specific location.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        try {
            $user = $request->user();

            $location = Location::with(['company', 'assignedWorker', 'jobLogs' => function ($query) {
                $query->latest()->limit(10);
            }])
            ->where('company_id', $user->company_id)
            ->where('id', $id)
            ->where('assigned_to', $user->id)
            ->first();

            if (!$location) {
                return response()->json([
                    'success' => false,
                    'message' => 'Location not found',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'location' => [
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
                    'metadata' => $location->metadata,
                    'company' => $location->company ? [
                        'id' => $location->company->id,
                        'name' => $location->company->name,
                    ] : null,
                    'assigned_worker' => $location->assignedWorker ? [
                        'id' => $location->assignedWorker->id,
                        'name' => $location->assignedWorker->name,
                        'phone' => $location->assignedWorker->phone,
                    ] : null,
                    'recent_jobs' => $location->jobLogs->map(function ($job) {
                        return [
                            'id' => $job->id,
                            'status' => $job->status->value,
                            'status_label' => $job->status->label(),
                            'completed_at' => $job->completed_at?->toIso8601String(),
                            'notes' => $job->notes,
                        ];
                    }),
                    'created_at' => $location->created_at->toIso8601String(),
                    'updated_at' => $location->updated_at->toIso8601String(),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch location: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch location',
            ], 500);
        }
    }

    /**
     * Update location status.
     */
    public function updateStatus(UpdateLocationStatusRequest $request, int $id): JsonResponse
    {
        try {
            $user = $request->user();

            $location = Location::where('company_id', $user->company_id)
                ->where('id', $id)
                ->where('assigned_to', $user->id)
                ->first();

            if (!$location) {
                return response()->json([
                    'success' => false,
                    'message' => 'Location not found',
                ], 404);
            }

            // Validate status transition
            $currentStatus = $location->status;
            $newStatus = $request->status;

            if (!$currentStatus->canTransitionTo(\App\Enums\JobStatus::from($newStatus))) {
                return response()->json([
                    'success' => false,
                    'message' => "Cannot transition from {$currentStatus->label()} to " . ucfirst(str_replace('_', ' ', $newStatus)),
                ], 422);
            }

            $location->update(['status' => $newStatus]);

            return response()->json([
                'success' => true,
                'message' => 'Location status updated successfully',
                'location' => [
                    'id' => $location->id,
                    'status' => $location->status->value,
                    'status_label' => $location->status->label(),
                    'updated_at' => $location->updated_at->toIso8601String(),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to update location status: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update location status',
            ], 500);
        }
    }
}