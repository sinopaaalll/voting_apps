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

    public function test_employee_can_login_with_registered_nik_but_unknown_nik_is_rejected(): void
    {
        $employee = Employee::create([
            'nik' => 'EMP-001', 'name' => 'Andi', 'department' => 'IT',
            'employment_status' => 'tetap', 'position' => 'Developer',
        ]);

        $this->post('/login', ['nik' => 'UNKNOWN'])
            ->assertSessionHasErrors('nik');

        $this->post('/login', ['nik' => 'emp-001'])
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
}
