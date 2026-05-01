<?php

namespace App\Jobs;

use App\Models\Student;
use App\Models\Behavior;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class EarlyWarningDetectionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Analyze patterns: 3 tardies + 2 missing assignments + 1 negative behavior = "Check-in recommended"
     */
    public function handle()
    {
        Student::query()->chunk(100, function ($students) {
            foreach ($students as $student) {
                $negativeBehaviorsCount = Behavior::where('student_id', $student->id)
                    ->where('type', 'Needs Improvement')
                    ->where('created_at', '>=', now()->subWeek())
                    ->count();

                // Logic: 3 tardies AND 2 missing assignments AND 1 negative behavior
                if ($student->tardies_count >= 3 &&
                    $student->missing_assignments_count >= 2 &&
                    $negativeBehaviorsCount >= 1) {

                    $this->triggerIntervention($student);
                }
            }
        });
    }

    protected function triggerIntervention(Student $student)
    {
        Log::warning("Intervention Recommended for Student ID: {$student->id} ({$student->first_name} {$student->last_name})");
        // Logic to add to Intervention Queue in DB or notify counselor
    }
}
