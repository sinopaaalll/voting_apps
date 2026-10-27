<?php

namespace Database\Seeders;

use App\Models\Employee;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Employee::query()->insert([
            ['nik' => 'EMP-001', 'name' => 'Andi Pratama', 'birth_date' => '1990-01-31', 'department' => 'Operasional', 'employment_status' => 'tetap', 'position' => 'Supervisor', 'created_at' => now(), 'updated_at' => now()],
            ['nik' => 'EMP-002', 'name' => 'Siti Rahma', 'birth_date' => '1992-05-18', 'department' => 'Human Resources', 'employment_status' => 'kontrak', 'position' => 'HR Specialist', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
