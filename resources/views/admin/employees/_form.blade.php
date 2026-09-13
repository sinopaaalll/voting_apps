<div class="form-grid">
    <label class="field"><span>NIK</span><input name="nik" value="{{ old('nik', $employee->nik ?? '') }}" maxlength="30" placeholder="EMP-001" required></label>
    <label class="field"><span>Nama lengkap</span><input name="name" value="{{ old('name', $employee->name ?? '') }}" maxlength="100" required></label>
    <label class="field"><span>Department</span><input name="department" value="{{ old('department', $employee->department ?? '') }}" maxlength="100" placeholder="Contoh: Human Resources" required></label>
    <label class="field"><span>Jabatan</span><input name="position" value="{{ old('position', $employee->position ?? '') }}" maxlength="100" placeholder="Contoh: HR Specialist" required></label>
    <label class="field field-wide">
        <span>Status karyawan</span>
        <select class="js-employment-status-select" name="employment_status" data-placeholder="Pilih status" required>
            <option value="">Pilih status</option>
            @foreach (['tetap' => 'Tetap', 'kontrak' => 'Kontrak', 'magang' => 'Magang'] as $value => $label)
                <option value="{{ $value }}" @selected(old('employment_status', $employee->employment_status ?? '') === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </label>
</div>
<div class="form-actions"><a class="btn btn-ghost" href="{{ route('admin.employees.index') }}">Batal</a><button class="btn btn-primary" type="submit">{{ $submitLabel }}</button></div>
