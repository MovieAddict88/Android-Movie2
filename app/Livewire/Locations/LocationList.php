<?php

namespace App\Livewire\Locations;

use App\Enums\JobStatus;
use App\Models\Company;
use App\Models\Location;
use App\Models\User;
use App\Services\FcmService;
use Illuminate\Support\Facades\Http;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class LocationList extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $statusFilter = '';

    #[Url]
    public ?string $dateFilter = null;

    public function mount(): void
    {
        $this->dateFilter = $this->dateFilter ?? now()->toDateString();
    }

    public function render()
    {
        $company = auth()->user()->company;

        $query = Location::where('company_id', $company->id)
            ->with(['assignedWorker:id,name'])
            ->when($this->search, function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('address', 'like', '%' . $this->search . '%');
            })
            ->when($this->statusFilter, function ($q) {
                $q->where('status', $this->statusFilter);
            })
            ->when($this->dateFilter, function ($q) {
                $q->whereDate('scheduled_date', $this->dateFilter);
            })
            ->orderBy('scheduled_date', 'desc')
            ->orderBy('scheduled_time_start', 'asc');

        $locations = $query->paginate(20);
        $workers = User::where('company_id', $company->id)
            ->workers()
            ->active()
            ->get();

        $statusCounts = Location::where('company_id', $company->id)
            ->when($this->dateFilter, function ($q) {
                $q->whereDate('scheduled_date', $this->dateFilter);
            })
            ->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        return view('livewire.locations.location-list', [
            'locations' => $locations,
            'workers' => $workers,
            'statusCounts' => $statusCounts,
        ]);
    }

    public function getStatusColor(string $status): string
    {
        return match ($status) {
            'pending' => 'gray',
            'assigned' => 'blue',
            'in_progress' => 'yellow',
            'completed' => 'green',
            'cancelled' => 'red',
            'failed' => 'red',
            default => 'gray',
        };
    }
}
