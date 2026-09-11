<?php

namespace App\Services;

use App\Models\Employee;
use DateInterval;
use DateTimeInterface;
use Generator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use OpenSpout\Reader\CSV\Reader as CsvReader;
use OpenSpout\Reader\ReaderInterface;
use OpenSpout\Reader\XLSX\Reader as XlsxReader;
use PhpOffice\PhpSpreadsheet\IOFactory;

class EmployeeSpreadsheetImporter
{
    private const BATCH_SIZE = 500;

    public function import(UploadedFile $file): array
    {
        set_time_limit(0);

        return DB::transaction(function () use ($file): array {
            $header = null;
            $columns = [];
            $batch = [];
            $created = 0;
            $updated = 0;
            $dataRowCount = 0;

            foreach ($this->rows($file) as $rowNumber => $row) {
                if ($header === null) {
                    $header = $row;
                    $columns = $this->resolveColumns($header);

                    continue;
                }

                if ($this->isEmptyRow($row)) {
                    continue;
                }

                $data = $this->prepareRow($row, $columns, $rowNumber);
                $batch[$data['nik']] = $data;
                $dataRowCount++;

                if (count($batch) >= self::BATCH_SIZE) {
                    $this->upsertBatch(array_values($batch), $created, $updated);
                    $batch = [];
                }
            }

            if ($header === null) {
                throw ValidationException::withMessages(['file' => 'File tidak berisi data employee.']);
            }

            if ($batch !== []) {
                $this->upsertBatch(array_values($batch), $created, $updated);
            }

            if ($dataRowCount === 0) {
                throw ValidationException::withMessages(['file' => 'Tidak ada baris employee yang dapat diimpor.']);
            }

            return ['created' => $created, 'updated' => $updated];
        }, 3);
    }

    private function prepareRow(array $row, array $columns, int $rowNumber): array
    {
        $data = [
            'nik' => Str::upper(trim($this->stringValue($row[$columns['nik']] ?? ''))),
            'name' => trim($this->stringValue($row[$columns['name']] ?? '')),
            'department' => trim($this->stringValue($row[$columns['department']] ?? '')),
            'employment_status' => Str::lower(trim($this->stringValue($row[$columns['employment_status']] ?? ''))),
            'position' => trim($this->stringValue($row[$columns['position']] ?? '')),
        ];

        $this->validatePreparedRow($data, $rowNumber);

        return $data;
    }

    private function validatePreparedRow(array $data, int $rowNumber): void
    {
        $labels = [
            'nik' => 'NIK',
            'name' => 'Nama',
            'department' => 'Department',
            'employment_status' => 'Status karyawan',
            'position' => 'Jabatan',
        ];
        $limits = ['nik' => 30, 'name' => 100, 'department' => 100, 'employment_status' => 20, 'position' => 100];
        $errors = [];

        foreach ($labels as $field => $label) {
            if ($data[$field] === '') {
                $errors[] = "{$label} wajib diisi.";
            } elseif (mb_strlen($data[$field]) > $limits[$field]) {
                $errors[] = "{$label} melebihi batas karakter.";
            }
        }

        if ($data['nik'] !== '' && preg_match('/^[A-Z0-9.\/-]+$/', $data['nik']) !== 1) {
            $errors[] = 'Format NIK tidak valid.';
        }

        if ($data['employment_status'] !== '' && ! in_array($data['employment_status'], ['tetap', 'kontrak', 'magang'], true)) {
            $errors[] = 'Status harus tetap, kontrak, atau magang.';
        }

        if ($errors !== []) {
            throw ValidationException::withMessages([
                'file' => array_map(fn (string $message) => "Baris {$rowNumber}: {$message}", $errors),
            ]);
        }
    }

    private function upsertBatch(array $rows, int &$created, int &$updated): void
    {
        $niks = array_column($rows, 'nik');
        $existingCount = Employee::whereIn('nik', $niks)->count();
        $timestamp = now();

        $records = array_map(fn (array $row) => [
            ...$row,
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ], $rows);

        Employee::upsert(
            $records,
            ['nik'],
            ['name', 'department', 'employment_status', 'position', 'updated_at'],
        );

        $created += count($rows) - $existingCount;
        $updated += $existingCount;
    }

    private function rows(UploadedFile $file): Generator
    {
        $extension = Str::lower($file->getClientOriginalExtension());

        if ($extension === 'xls') {
            yield from $this->legacyXlsRows($file);

            return;
        }

        $reader = $extension === 'csv' ? new CsvReader : new XlsxReader;
        yield from $this->streamRows($reader, $file->getRealPath());
    }

    private function streamRows(ReaderInterface $reader, string $path): Generator
    {
        $reader->open($path);
        $rowNumber = 0;

        try {
            foreach ($reader->getSheetIterator() as $sheet) {
                foreach ($sheet->getRowIterator() as $row) {
                    $rowNumber++;
                    yield $rowNumber => $row->toArray();
                }

                break;
            }
        } finally {
            $reader->close();
        }
    }

    private function legacyXlsRows(UploadedFile $file): Generator
    {
        $reader = IOFactory::createReader('Xls');
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($file->getRealPath());

        try {
            foreach ($spreadsheet->getActiveSheet()->getRowIterator() as $row) {
                $values = [];
                foreach ($row->getCellIterator() as $cell) {
                    $values[] = $cell->getValue();
                }

                yield $row->getRowIndex() => $values;
            }
        } finally {
            $spreadsheet->disconnectWorksheets();
        }
    }

    private function resolveColumns(array $header): array
    {
        $normalized = array_map(
            fn ($value) => Str::of($this->stringValue($value))->trim()->lower()->replace([' ', '-'], '_')->value(),
            $header,
        );

        $aliases = [
            'nik' => ['nik'],
            'name' => ['name', 'nama'],
            'department' => ['department', 'departemen'],
            'employment_status' => ['employment_status', 'status_karyawan', 'status'],
            'position' => ['position', 'jabatan'],
        ];
        $columns = [];
        $missing = [];

        foreach ($aliases as $field => $acceptedHeaders) {
            $column = null;

            foreach ($acceptedHeaders as $acceptedHeader) {
                $found = array_search($acceptedHeader, $normalized, true);
                if ($found !== false) {
                    $column = $found;
                    break;
                }
            }

            if ($column === null) {
                $missing[] = $acceptedHeaders[0];
            } else {
                $columns[$field] = $column;
            }
        }

        if ($missing !== []) {
            throw ValidationException::withMessages([
                'file' => 'Header tidak lengkap. Kolom yang belum ada: '.implode(', ', $missing).'.',
            ]);
        }

        return $columns;
    }

    private function stringValue(mixed $value): string
    {
        return match (true) {
            $value instanceof DateTimeInterface => $value->format('Y-m-d H:i:s'),
            $value instanceof DateInterval => '',
            is_bool($value) => $value ? '1' : '0',
            is_scalar($value) => (string) $value,
            default => '',
        };
    }

    private function isEmptyRow(array $row): bool
    {
        foreach ($row as $value) {
            if (trim($this->stringValue($value)) !== '') {
                return false;
            }
        }

        return true;
    }
}
