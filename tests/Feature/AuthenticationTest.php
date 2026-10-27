<?php

namespace Tests\Feature;

use App\Models\Employee;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_protected_pages_redirect_guests_to_the_correct_login(): void
    {
        $this->get('/voting')->assertRedirect(route('employee.login'));
        $this->get('/admin')->assertRedirect(route('admin.login'));
        $this->get(route('admin.results.export'))->assertRedirect(route('admin.login'));
    }

    public function test_employee_can_login_only_with_matching_nik_and_birth_date(): void
    {
        $employee = Employee::create([
            'nik' => 'EMP-001', 'name' => 'Andi', 'department' => 'IT',
            'birth_date' => '1990-01-31', 'employment_status' => 'tetap', 'position' => 'Developer',
        ]);

        $this->post('/login', ['nik' => 'UNKNOWN', 'birth_date' => '1990-01-31'])
            ->assertSessionHasErrors('nik');

        $this->post('/login', ['nik' => 'emp-001', 'birth_date' => '1990-02-01'])
            ->assertSessionHasErrors('nik')
            ->assertSessionMissing('employee_id');

        $this->post('/login', ['nik' => 'emp-001', 'birth_date' => '1990-01-31'])
            ->assertRedirect(route('voting.index'))
            ->assertSessionHas('employee_id', $employee->id);
    }

    public function test_admin_can_login_using_configured_credentials(): void
    {
        config()->set('voting.admin.username', 'admin-test');
        config()->set('voting.admin.password_hash', Hash::make('rahasia-test'));

        $this->post('/admin/login', ['username' => 'admin-test', 'password' => 'salah'])
            ->assertSessionHasErrors('username');

        $this->post('/admin/login', ['username' => 'admin-test', 'password' => 'rahasia-test'])
            ->assertRedirect(route('admin.dashboard'))
            ->assertSessionHas('admin_authenticated', true);
    }

    public function test_admin_login_with_invalid_password_hash_is_rejected_without_an_exception(): void
    {
        config()->set('voting.admin.username', 'admin-test');
        config()->set('voting.admin.password_hash', '$2a$10$invalid-bcrypt-hash');

        $this->post('/admin/login', ['username' => 'admin-test', 'password' => 'rahasia-test'])
            ->assertSessionHasErrors('username')
            ->assertSessionMissing('admin_authenticated');
    }
}
