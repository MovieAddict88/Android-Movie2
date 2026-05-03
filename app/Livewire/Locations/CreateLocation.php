<?php

namespace App\Livewire\Locations;

use App\Enums\JobStatus;
use App\Models\Company;
use App\Models\Location;
use App\Models\User;
use App\Notifications\JobAssigned;
use App\Services\FcmService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Rule;
use Livewire\Component;

class CreateLocation extends Component
{
    #[Rule('required|string|max:255')]
    public string $name = '';

    #[Rule('required|string|max:500')]
    public string $address = '';

    #[Rule('nullable|numeric')]
    public float $latitude = 0;

    #[Rule('nullable|numeric')]
    public float $longitude = 0;

    #[Rule('nullable|integer|min:10|max:1000')]
    public int $radius = 100;

    #[Rule('nullable|exists:users,id')]
    public ?int $assignedTo = null;

    #[Rule('nullable|date')]
    public ?string $scheduledDate = null;

    #[Rule('nullable|date_format:H:i')]
    public ?string $scheduledTimeStart = null;

    #[Rule('nullable|date_format:H:i')]
    public ?string $scheduledTimeEnd = null;

    #[Rule('nullable|string')]
    public string $notes = '';

    public bool $isGeocoding = false;
    public bool $showSuccess = false;

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
                } else {
                    $this->dispatch('notify', [
                        'type' => 'error',
                        'message' => 'Could not find coordinates for this address.',
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error('Geocoding error: ' . $e->getMessage());
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Geocoding failed. Please enter coordinates manually.',
            ]);
        }

        $this->isGeocoding = false;
    }

    public function save(): void
    {
        $this->validate();

        $company = auth()->user()->company;

        $location = Location::create([
            'company_id' => $company->id,
            'name' => $this->name,
            'address' => $this->address,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'radius' => $this->radius,
            'assigned_to' => $this->assignedTo,
            'status' => $this->assignedTo ? JobStatus::ASSIGNED : JobStatus::PENDING,
            'scheduled_date' => $this->scheduledDate,
            'scheduled_time_start' => $this->scheduledTimeStart,
            'scheduled_time_end' => $this->scheduledTimeEnd,
            'notes' => $this->notes,
        ]);

        if ($this->assignedTo) {
            $worker = User::find($this->assignedTo);
            if ($worker) {
                $worker->notify(new JobAssigned($location));
            }
        }

        $this->showSuccess = true;
        $this->reset([
            'name', 'address', 'latitude', 'longitude', 'radius',
            'assignedTo', 'scheduledDate', 'scheduledTimeStart',
            'scheduledTimeEnd', 'notes'
        ]);

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Location created successfully!',
        ]);
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
        return view('livewire.locations.create-location', [
            'workers' => $this->workers,
        ]);
    }
}
