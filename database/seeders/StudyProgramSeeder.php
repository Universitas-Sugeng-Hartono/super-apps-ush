<?php

namespace Database\Seeders;

use App\Models\StudyProgram;
use Illuminate\Database\Seeder;

class StudyProgramSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $programs = [
            [
                'name' => 'Bisnis Digital',
                'code' => 'BD',
                'is_active' => true,
                'order' => 1,
            ],
            [
                'name' => 'Ilmu Komputer',
                'code' => 'IK',
                'is_active' => true,
                'order' => 2,
            ],
            [
                'name' => 'Gizi',
                'code' => 'GZ',
                'is_active' => true,
                'order' => 3,
            ],
        ];

        foreach ($programs as $program) {
            StudyProgram::updateOrCreate(
                ['name' => $program['name']],
                $program
            );
        }
    }
}
