<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\JobLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SyncController extends Controller
{
    public function getLocations(Request $request)
    {
        $user = $request->user();
        $locations = Location::where('company_id', $user->company_id)->get();
        return response()->json($locations);
    }

    public function uploadJobLog(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'location_id' => [
                'required',
                Rule::exists('locations', 'id')->where(function ($query) use ($user) {
                    $query->where('company_id', $user->company_id);
                }),
            ],
            'check_in_at' => 'required|date',
            'check_out_at' => 'required|date|after:check_in_at',
            'notes' => 'nullable|string|max:1000',
            'photo_base64' => 'nullable|string',
        ]);

        $photoPath = null;
        if ($request->photo_base64) {
            // Basic validation for base64 image could be added here
            $photoData = base64_decode($request->photo_base64);
            if ($photoData) {
                $fileName = 'job_photos/' . Str::random(40) . '.jpg';
                Storage::disk('public')->put($fileName, $photoData);
                $photoPath = $fileName;
            }
        }

        $jobLog = JobLog::create([
            'user_id' => $user->id,
            'location_id' => $request->location_id,
            'check_in_at' => $request->check_in_at,
            'check_out_at' => $request->check_out_at,
            'notes' => $request->notes,
            'photo_path' => $photoPath,
        ]);

        return response()->json([
            'message' => 'Job log uploaded successfully',
            'job_log_id' => $jobLog->id,
        ], 201);
    }

    public function sync(Request $request)
    {
        $user = $request->user();
        $lastSync = $request->query('last_sync');

        $query = Location::where('company_id', $user->company_id);
        if ($lastSync) {
            $query->where('updated_at', '>', $lastSync);
        }

        return response()->json([
            'locations' => $query->get(),
            'server_time' => now()->toDateTimeString(),
        ]);
    }
}
