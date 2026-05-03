<?php

namespace App\Livewire\Locations;

use App\Enums\JobStatus;
use App\Models\Location;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class EditLocation extends Component
{
    public Location $location;

    public string $name = '';
    public string $address = '';
    public float $latitude = 0;
    public float $longitude = 0;
    public int $radius = 100;
    public ?int $assignedTo = null;
    public ?string $scheduledDate = null;
    public ?string $scheduledTimeStart = null;
    public ?string $scheduledTimeEnd = null;
    public string $notes = '';
    public string $status = 'pending';

    public bool $isGeocoding = false;

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:500',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'radius' => 'nullable|integer|min:10|max:1000',
            'assignedTo' => 'nullable|exists:users,id',
            'scheduledDate' => 'nullable|date',
            'scheduledTimeStart' => 'nullable|date_format:H:i',
            'scheduledTimeEnd' => 'nullable|date_format:H:i',
            'notes' => 'nullable|string',
            'status' => 'required|in:pending,assigned,in_progress,completed,cancelled,failed',
        ];
    }

    public function mount(Location $location): void
    {
        $this->location = $location;
        $this->name = $location->name;
        $this->address = $location->address;
        $this->latitude = $location->latitude;
        $this->longitude = $location->longitude;
        $this->radius = $location->radius;
        $this->assignedTo = $location->assigned_to;
        $this->scheduledDate = $location->scheduled_date?->toDateString();
        $this->scheduledTimeStart = $location->scheduled_time_start;
        $this->scheduledTimeEnd = $location->scheduled_time_end;
        $this->notes = $location->notes ?? '';
        $this->status = $location->status->value;
    }

    public function geocodeAddress(): void
    {
        if (empty($this->address)) {
            return;
        }

        $this->isGeocoding = true;

        try {
            $apiKey = config('services.google_maps.api_key');
            $url = "https://maps.googleapis.com/maps/api/geocode/json?address=" . urlencode($this->address);

            if ($apiKey) {
                $url .= "&key={$apiKey}";
            }

            $response = Http::get($url);

            if ($response->successful()) {
                $data = $response->json();

                if (!empty($data['results'][0]['geometry']['location'])) {
                    $location = $data['results'][0]['geometry']['location'];
                    $this->latitude = $location['lat'];
                    $this->longitude = $location['lng'];

                    $this->dispatch('notify', [
                        'type' => 'success',
                        'message' => 'Address geocoded successfully!',
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error('Geocoding error: ' . $e->getMessage());
        }

        $this->isGeocoding = false;
    }

    public function save(): void
    {
        $this->validate();

        $this->location->update([
            'name' => $this->name,
            'address' => $this->address,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'radius' => $this->radius,
            'assigned_to' => $this->assignedTo,
            'status' => JobStatus::from($this->status),
            'scheduled_date' => $this->scheduledDate,
            'scheduled_time_start' => $this->scheduledTimeStart,
            'scheduled_time_end' => $this->scheduledTimeEnd,
            'notes' => $this->notes,
        ]);

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Location updated successfully!',
        ]);

        $this->redirectRoute('locations.index');
    }

    public function getWorkersProperty()
    {
        $company = auth()->user()->company;
        return User::where('company_id', $company->id)
            ->workers()
            ->active()
            ->get();
    }

    public function render()
    {
        return view('livewire.locations.edit-location', [
            'workers' => $this->workers,
        ]);
    }
}
