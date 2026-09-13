<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\Kandidat;
use App\Models\Voting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class AdminManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_employee_and_duplicate_nik_is_rejected(): void
    {
        $data = [
            'nik' => 'emp-001', 'name' => 'Andi Pratama', 'department' => 'IT',
            'employment_status' => 'tetap', 'position' => 'Developer',
        ];

        $this->withSession(['admin_authenticated' => true])->post(route('admin.employees.store'), $data)
            ->assertRedirect(route('admin.employees.index'));
        $this->assertDatabaseHas('employee', ['nik' => 'EMP-001']);

        $this->withSession(['admin_authenticated' => true])->post(route('admin.employees.store'), $data)
            ->assertSessionHasErrors('nik');
    }

    public function test_candidate_photo_validation_and_storage_work(): void
    {
        Storage::fake('public');

        $this->withSession(['admin_authenticated' => true])->post(route('admin.kandidat.store'), [
            'nomor_urut' => 1, 'name' => 'Kandidat Satu',
            'photo' => UploadedFile::fake()->create('bukan-gambar.txt', 10, 'text/plain'),
        ])->assertSessionHasErrors('photo');

        $response = $this->withSession(['admin_authenticated' => true])->post(route('admin.kandidat.store'), [
            'nomor_urut' => 1, 'name' => 'Kandidat Satu',
            'photo' => UploadedFile::fake()->image('foto.jpg', 400, 500),
        ]);

        $response->assertRedirect(route('admin.kandidat.index'));
        Storage::disk('public')->assertExists(Kandidat::firstOrFail()->photo);
    }

    public function test_records_with_votes_cannot_be_deleted_or_candidate_edited(): void
    {
        $employee = Employee::create(['nik' => 'EMP-001', 'name' => 'Andi', 'department' => 'IT', 'employment_status' => 'tetap', 'position' => 'Developer']);
        $candidate = Kandidat::create(['nomor_urut' => 1, 'name' => 'Kandidat', 'photo' => 'kandidat/1.jpg']);
        Voting::create(['employee_id' => $employee->id, 'kandidat_id' => $candidate->id]);

        $this->withSession(['admin_authenticated' => true])->delete(route('admin.employees.destroy', $employee))
            ->assertSessionHasErrors('delete');
        $this->withSession(['admin_authenticated' => true])->delete(route('admin.kandidat.destroy', $candidate))
            ->assertSessionHasErrors('delete');
        $this->withSession(['admin_authenticated' => true])->get(route('admin.kandidat.edit', $candidate))
            ->assertRedirect(route('admin.kandidat.index'));

        $this->assertDatabaseHas('employee', ['id' => $employee->id]);
        $this->assertDatabaseHas('kandidat', ['id' => $candidate->id]);
    }

    public function test_results_show_aggregate_counts_without_employee_identity(): void
    {
        $employee = Employee::create(['nik' => 'SECRET-001', 'name' => 'Nama Rahasia', 'department' => 'IT', 'employment_status' => 'tetap', 'position' => 'Developer']);
        $candidate = Kandidat::create(['nomor_urut' => 1, 'name' => 'Kandidat', 'photo' => 'kandidat/1.jpg']);
        Voting::create(['employee_id' => $employee->id, 'kandidat_id' => $candidate->id]);

        $this->withSession(['admin_authenticated' => true])->get(route('admin.results'))
            ->assertOk()
            ->assertSee('1 dari 1 suara masuk')
            ->assertSee('Kandidat')
            ->assertDontSee('SECRET-001')
            ->assertDontSee('Nama Rahasia');

        $this->withSession(['admin_authenticated' => true])->get(route('admin.results.show', $candidate))
            ->assertOk()
            ->assertSee('SECRET-001')
            ->assertSee('Nama Rahasia')
            ->assertSee('Developer');
    }

    public function test_admin_can_export_summary_and_voter_details_to_excel(): void
    {
        $employee = Employee::create(['nik' => '0012345', 'name' => 'Budi', 'department' => 'Quality', 'employment_status' => 'tetap', 'position' => 'Inspector']);
        $candidate = Kandidat::create(['nomor_urut' => 2, 'name' => 'Kandidat Dua', 'photo' => 'kandidat/2.jpg']);
        Voting::create(['employee_id' => $employee->id, 'kandidat_id' => $candidate->id]);

        $response = $this->withSession(['admin_authenticated' => true])->get(route('admin.results.export'));
        $response->assertOk()->assertDownload('hasil-voting.xlsx');

        $temporaryPath = tempnam(sys_get_temp_dir(), 'hasil-voting-');
        file_put_contents($temporaryPath, $response->streamedContent());
        $workbook = IOFactory::load($temporaryPath);

        try {
            $this->assertSame(['Ringkasan', 'Detail Pemilih'], $workbook->getSheetNames());
            $this->assertSame(1, $workbook->getSheetByName('Ringkasan')->getCell('B2')->getValue());
            $this->assertSame(1, $workbook->getSheetByName('Ringkasan')->getCell('D2')->getValue());
            $this->assertSame('Kandidat Dua', $workbook->getSheetByName('Ringkasan')->getCell('B6')->getValue());
            $this->assertSame('=IF($D$2=0,0,C6/$D$2)', $workbook->getSheetByName('Ringkasan')->getCell('D6')->getValue());
            $this->assertSame('0012345', $workbook->getSheetByName('Detail Pemilih')->getCell('B5')->getValue());
            $this->assertSame(DataType::TYPE_STRING, $workbook->getSheetByName('Detail Pemilih')->getCell('B5')->getDataType());
            $this->assertSame('Budi', $workbook->getSheetByName('Detail Pemilih')->getCell('C5')->getValue());
            $this->assertSame('Kandidat Dua', $workbook->getSheetByName('Detail Pemilih')->getCell('H5')->getValue());
        } finally {
            $workbook->disconnectWorksheets();
            unlink($temporaryPath);
        }
    }

    public function test_admin_can_import_excel_and_existing_nik_is_updated(): void
    {
        Employee::create(['nik' => 'EMP-001', 'name' => 'Nama Lama', 'department' => 'IT', 'employment_status' => 'tetap', 'position' => 'Developer']);
        $file = $this->excelUpload([
            ['nik', 'nama', 'departemen', 'status_karyawan', 'jabatan'],
            ['EMP-001', 'Nama Baru', 'Technology', 'Kontrak', 'Senior Developer'],
            ['EMP-003', 'Budi Santoso', 'Finance', 'Tetap', 'Finance Officer'],
        ]);

        $this->withSession(['admin_authenticated' => true])
            ->post(route('admin.employees.import'), ['file' => $file])
            ->assertRedirect()
            ->assertSessionHas('success', 'Import selesai: 1 employee ditambahkan dan 1 diperbarui.');

        $this->assertDatabaseHas('employee', ['nik' => 'EMP-001', 'name' => 'Nama Baru', 'employment_status' => 'kontrak']);
        $this->assertDatabaseHas('employee', ['nik' => 'EMP-003', 'department' => 'Finance']);
    }

    public function test_invalid_import_is_rejected_atomically(): void
    {
        $file = $this->excelUpload([
            ['nik', 'name', 'department', 'employment_status', 'position'],
            ['EMP-010', 'Data Valid', 'IT', 'tetap', 'Developer'],
            ['EMP-011', 'Data Salah', 'IT', 'freelance', 'Developer'],
        ]);

        $this->withSession(['admin_authenticated' => true])
            ->post(route('admin.employees.import'), ['file' => $file])
            ->assertSessionHasErrors('file');

        $this->assertDatabaseMissing('employee', ['nik' => 'EMP-010']);
        $this->assertDatabaseMissing('employee', ['nik' => 'EMP-011']);
    }

    public function test_excel_import_has_no_one_thousand_row_limit(): void
    {
        $rows = [['nik', 'name', 'department', 'employment_status', 'position']];
        foreach (range(1, 1205) as $number) {
            $rows[] = [sprintf('BULK-%05d', $number), "Employee {$number}", 'Operations', 'tetap', 'Staff'];
        }

        DB::flushQueryLog();
        DB::enableQueryLog();

        $this->withSession(['admin_authenticated' => true])
            ->post(route('admin.employees.import'), ['file' => $this->excelUpload($rows)])
            ->assertRedirect()
            ->assertSessionHas('success', 'Import selesai: 1205 employee ditambahkan dan 0 diperbarui.');

        $insertQueries = collect(DB::getQueryLog())->filter(
            fn (array $query) => str_starts_with(strtolower($query['query']), 'insert into')
                && str_contains(strtolower($query['query']), 'employee'),
        );

        $this->assertSame(1205, Employee::count());
        $this->assertCount(3, $insertQueries, '1205 rows should be written in three batched upsert queries.');
    }

    public function test_bulk_delete_skips_employees_who_have_voted(): void
    {
        $first = Employee::create(['nik' => 'EMP-101', 'name' => 'Satu', 'department' => 'IT', 'employment_status' => 'tetap', 'position' => 'Developer']);
        $second = Employee::create(['nik' => 'EMP-102', 'name' => 'Dua', 'department' => 'IT', 'employment_status' => 'tetap', 'position' => 'Developer']);
        $protected = Employee::create(['nik' => 'EMP-103', 'name' => 'Tiga', 'department' => 'IT', 'employment_status' => 'tetap', 'position' => 'Developer']);
        $candidate = Kandidat::create(['nomor_urut' => 1, 'name' => 'Kandidat', 'photo' => 'kandidat/1.jpg']);
        Voting::create(['employee_id' => $protected->id, 'kandidat_id' => $candidate->id]);

        $this->withSession(['admin_authenticated' => true])
            ->delete(route('admin.employees.bulk-destroy'), ['employee_ids' => [$first->id, $second->id, $protected->id]])
            ->assertRedirect()
            ->assertSessionHas('success', '2 employee berhasil dihapus. 1 employee dilewati karena sudah memilih.');

        $this->assertDatabaseMissing('employee', ['id' => $first->id]);
        $this->assertDatabaseMissing('employee', ['id' => $second->id]);
        $this->assertDatabaseHas('employee', ['id' => $protected->id]);
    }

    public function test_admin_can_download_excel_import_template(): void
    {
        $this->withSession(['admin_authenticated' => true])
            ->get(route('admin.employees.template'))
            ->assertOk()
            ->assertDownload('template-import-employee.xlsx');
    }

    private function excelUpload(array $rows): UploadedFile
    {
        $spreadsheet = new Spreadsheet;
        $spreadsheet->getActiveSheet()->fromArray($rows);
        $temporaryPath = tempnam(sys_get_temp_dir(), 'employee-import-');
        (new Xlsx($spreadsheet))->save($temporaryPath);
        $contents = file_get_contents($temporaryPath);
        unlink($temporaryPath);
        $spreadsheet->disconnectWorksheets();

        return UploadedFile::fake()->createWithContent('employees.xlsx', $contents);
    }
}
