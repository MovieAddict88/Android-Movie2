<?php

namespace Database\Seeders;

use App\Models\School;
use App\Models\Student;
use App\Models\Behavior;
use Illuminate\Database\Seeder;

class DemoSeeder extends Seeder
{
    public function run()
    {
        $school = School::create([
            'name' => 'Demo Springfield Elementary',
            'subdomain' => 'demo',
            'database_name' => 'demo_school_db',
            'is_demo' => true,
        ]);

        $student = Student::create([
            'first_name' => 'Alex',
            'last_name' => 'Demo',
            'student_id_number' => 'DEMO001',
            'school_id' => $school->id,
            'tardies_count' => 3,
            'missing_assignments_count' => 2,
        ]);

        Behavior::create([
            'student_id' => $student->id,
            'teacher_id' => 1,
            'type' => 'Needs Improvement',
            'category' => 'Talking Out',
            'note' => 'Alex was talking during the demo.',
            'points' => -5,
        ]);
    }
}
