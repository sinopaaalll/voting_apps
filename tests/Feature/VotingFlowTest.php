<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\Kandidat;
use App\Models\Voting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VotingFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_can_vote_once_and_the_vote_is_final(): void
    {
        $employee = $this->employee();
        $first = $this->kandidat(1, 'Kandidat Satu');
        $second = $this->kandidat(2, 'Kandidat Dua');

        $this->withSession(['employee_id' => $employee->id])
            ->post('/voting', ['kandidat_id' => $first->id, 'confirmation' => '1'])
            ->assertRedirect(route('voting.index'));

        $this->assertDatabaseHas('voting', ['employee_id' => $employee->id, 'kandidat_id' => $first->id]);

        $this->withSession(['employee_id' => $employee->id])
            ->post('/voting', ['kandidat_id' => $second->id, 'confirmation' => '1'])
            ->assertSessionHasErrors('vote');

        $this->assertSame(1, Voting::count());
        $this->withSession(['employee_id' => $employee->id])
            ->get('/voting')
            ->assertOk()
            ->assertSee('Suara tercatat')
            ->assertDontSee('Kandidat Satu');
    }

    public function test_invalid_candidate_and_missing_confirmation_are_rejected(): void
    {
        $employee = $this->employee();

        $this->withSession(['employee_id' => $employee->id])
            ->post('/voting', ['kandidat_id' => 999, 'confirmation' => '1'])
            ->assertSessionHasErrors('kandidat_id');

        $candidate = $this->kandidat(1, 'Kandidat Satu');
        $this->withSession(['employee_id' => $employee->id])
            ->post('/voting', ['kandidat_id' => $candidate->id])
            ->assertSessionHasErrors('confirmation');
    }

    private function employee(): Employee
    {
        return Employee::create([
            'nik' => 'EMP-001', 'name' => 'Andi', 'department' => 'IT',
            'employment_status' => 'tetap', 'position' => 'Developer',
        ]);
    }

    private function kandidat(int $number, string $name): Kandidat
    {
        return Kandidat::create([
            'nomor_urut' => $number, 'name' => $name,
            'photo' => "kandidat/{$number}.jpg", 'visi_misi' => 'Membangun perusahaan yang lebih baik.',
        ]);
    }
}
