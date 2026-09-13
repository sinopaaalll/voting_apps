<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\Kandidat;
use App\Models\Voting;
use Database\Seeders\TruncateVotingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TruncateVotingSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_only_clears_voting_results(): void
    {
        $employee = Employee::create([
            'nik' => 'EMP-RESET',
            'name' => 'Employee Reset',
            'department' => 'IT',
            'employment_status' => 'tetap',
            'position' => 'Developer',
        ]);
        $candidate = Kandidat::create([
            'nomor_urut' => 1,
            'name' => 'Kandidat Reset',
            'photo' => 'kandidat/reset.jpg',
        ]);
        Voting::create(['employee_id' => $employee->id, 'kandidat_id' => $candidate->id]);

        $this->seed(TruncateVotingSeeder::class);

        $this->assertDatabaseCount('voting', 0);
        $this->assertDatabaseHas('employee', ['id' => $employee->id]);
        $this->assertDatabaseHas('kandidat', ['id' => $candidate->id]);
    }
}
