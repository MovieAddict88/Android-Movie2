<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\JobLog;
use Illuminate\Support\Facades\Auth;

class JobLogDashboard extends Component
{
    public function render()
    {
        $jobLogs = JobLog::with(['user', 'location'])
            ->whereHas('user', function ($query) {
                $query->where('company_id', Auth::user()->company_id);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.job-log-dashboard', [
            'jobLogs' => $jobLogs
        ]);
    }
}
