<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DatabaseSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_contains_only_the_three_domain_tables(): void
    {
        foreach (['employee', 'kandidat', 'voting'] as $table) {
            $this->assertTrue(Schema::hasTable($table));
        }

        foreach (['users', 'sessions', 'cache', 'jobs'] as $table) {
            $this->assertFalse(Schema::hasTable($table));
        }

        $this->assertTrue(Schema::hasColumns('employee', ['nik', 'name', 'department', 'employment_status', 'position']));
        $this->assertTrue(Schema::hasColumns('kandidat', ['nomor_urut', 'name', 'photo']));
        $this->assertFalse(Schema::hasColumn('kandidat', 'visi_misi'));
        $this->assertTrue(Schema::hasColumns('voting', ['employee_id', 'kandidat_id']));
    }
}
