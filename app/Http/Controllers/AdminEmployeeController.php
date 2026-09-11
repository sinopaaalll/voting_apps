<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Services\EmployeeSpreadsheetImporter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class AdminEmployeeController extends Controller
{
    public function index(Request $request): View
    {
        $employees = Employee::query()
            ->withExists('voting')
            ->when($request->filled('q'), function ($query) use ($request): void {
                $search = $request->string('q')->trim()->value();
                $query->where(function ($query) use ($search): void {
                    $query->where('nik', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('department'), fn ($query) => $query->where('department', $request->string('department')->value()))
            ->when($request->filled('status'), fn ($query) => $query->where('employment_status', $request->string('status')->value()))
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        $departments = Employee::query()->select('department')->distinct()->orderBy('department')->pluck('department');

        return view('admin.employees.index', compact('employees', 'departments'));
    }

    public function create(): View
    {
        return view('admin.employees.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedData($request);
        $data['nik'] = Str::upper(trim($data['nik']));
        Employee::create($data);

        return redirect()->route('admin.employees.index')->with('success', 'Employee berhasil ditambahkan.');
    }

    public function edit(Employee $employee): View
    {
        return view('admin.employees.edit', compact('employee'));
    }

    public function update(Request $request, Employee $employee): RedirectResponse
    {
        $data = $this->validatedData($request, $employee);
        $data['nik'] = Str::upper(trim($data['nik']));
        $employee->update($data);

        return redirect()->route('admin.employees.index')->with('success', 'Data employee berhasil diperbarui.');
    }

    public function destroy(Employee $employee): RedirectResponse
    {
        if ($employee->voting()->exists()) {
            return back()->withErrors(['delete' => 'Employee yang sudah memilih tidak dapat dihapus.']);
        }

        $employee->delete();

        return back()->with('success', 'Employee berhasil dihapus.');
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'employee_ids' => ['required', 'array', 'min:1'],
            'employee_ids.*' => ['integer', 'distinct', 'exists:employee,id'],
        ], [
            'employee_ids.required' => 'Pilih setidaknya satu employee.',
            'employee_ids.min' => 'Pilih setidaknya satu employee.',
            'employee_ids.*.exists' => 'Terdapat employee yang sudah tidak tersedia.',
        ]);

        $ids = $validated['employee_ids'];
        $protectedCount = Employee::whereIn('id', $ids)->whereHas('voting')->count();
        $deletedCount = DB::transaction(
            fn () => Employee::whereIn('id', $ids)->whereDoesntHave('voting')->delete(),
        );

        if ($deletedCount === 0) {
            return back()->withErrors(['delete' => 'Employee yang dipilih sudah memberikan suara sehingga tidak dapat dihapus.']);
        }

        $message = "{$deletedCount} employee berhasil dihapus.";
        if ($protectedCount > 0) {
            $message .= " {$protectedCount} employee dilewati karena sudah memilih.";
        }

        return back()->with('success', $message);
    }

    public function import(Request $request, EmployeeSpreadsheetImporter $importer): RedirectResponse
    {
        $validated = $request->validate([
            'file' => ['required', 'file', 'extensions:xlsx,xls,csv'],
        ], [
            'file.required' => 'Pilih file employee yang akan diimpor.',
            'file.extensions' => 'File harus berformat XLSX, XLS, atau CSV.',
        ]);

        try {
            $result = $importer->import($validated['file']);
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            report($exception);

            return back()->withErrors(['file' => 'File tidak dapat dibaca. Pastikan format dan isinya sesuai template.']);
        }

        return back()->with('success', "Import selesai: {$result['created']} employee ditambahkan dan {$result['updated']} diperbarui.");
    }

    public function template(): StreamedResponse
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Employee');
        $sheet->fromArray([
            ['nik', 'name', 'department', 'employment_status', 'position'],
            ['EMP-003', 'Budi Santoso', 'Finance', 'tetap', 'Finance Officer'],
        ]);
        $sheet->getStyle('A1:E1')->getFont()->setBold(true);
        foreach (range('A', 'E') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        return response()->streamDownload(function () use ($spreadsheet): void {
            (new Xlsx($spreadsheet))->save('php://output');
            $spreadsheet->disconnectWorksheets();
        }, 'template-import-employee.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    private function validatedData(Request $request, ?Employee $employee = null): array
    {
        $normalizedNik = Str::upper(trim((string) $request->input('nik')));
        $request->merge(['nik' => $normalizedNik]);

        return $request->validate([
            'nik' => [
                'required',
                'string',
                'max:30',
                'regex:/^[A-Z0-9.\/-]+$/',
                Rule::unique('employee', 'nik')->ignore($employee?->id),
            ],
            'name' => ['required', 'string', 'max:100'],
            'department' => ['required', 'string', 'max:100'],
            'employment_status' => ['required', Rule::in(['tetap', 'kontrak', 'magang'])],
            'position' => ['required', 'string', 'max:100'],
        ], [
            'nik.required' => 'NIK wajib diisi.',
            'nik.regex' => 'NIK hanya boleh berisi huruf, angka, titik, garis miring, atau tanda hubung.',
            'nik.unique' => 'NIK sudah digunakan.',
            'name.required' => 'Nama employee wajib diisi.',
            'department.required' => 'Department wajib diisi.',
            'employment_status.required' => 'Status karyawan wajib dipilih.',
            'employment_status.in' => 'Status karyawan tidak valid.',
            'position.required' => 'Jabatan wajib diisi.',
            '*.max' => 'Isian melebihi batas karakter yang diperbolehkan.',
        ]);
    }
}
