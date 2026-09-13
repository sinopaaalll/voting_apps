@php($hasExistingPhoto = isset($kandidat) && filled($kandidat->photo))

<div class="form-grid">
    <label class="field"><span>Nomor urut</span><input type="number" name="nomor_urut" value="{{ old('nomor_urut', $kandidat->nomor_urut ?? '') }}" min="1" max="999" required></label>
    <label class="field"><span>Nama kandidat</span><input name="name" value="{{ old('name', $kandidat->name ?? '') }}" maxlength="100" required></label>
    <div class="field field-wide candidate-photo-field" data-image-upload>
        <span>Foto kandidat {{ isset($kandidat) ? '(kosongkan jika tidak diubah)' : '' }}</span>
        <div class="candidate-image-preview {{ $hasExistingPhoto ? 'has-image' : '' }}" data-image-preview>
            <img @if ($hasExistingPhoto) src="{{ Storage::url($kandidat->photo) }}" @endif alt="Preview foto kandidat" data-image-preview-image @if (! $hasExistingPhoto) hidden @endif>
            <div class="candidate-image-placeholder" data-image-placeholder @if ($hasExistingPhoto) hidden @endif>
                <span aria-hidden="true">IMG</span>
                <strong>Preview foto kandidat</strong>
                <small>Foto yang dipilih akan tampil di sini.</small>
            </div>
        </div>
        <label class="candidate-file-picker">
            <input class="candidate-file-input" type="file" name="photo" accept="image/jpeg,image/png,image/webp" data-image-input {{ isset($kandidat) ? '' : 'required' }}>
            <span class="candidate-file-button">Pilih foto</span>
            <span class="candidate-file-name" data-image-file-name>{{ $hasExistingPhoto ? basename($kandidat->photo) : 'Belum ada foto dipilih' }}</span>
        </label>
        <small>Hanya JPG, PNG, atau WebP. Maksimal 2 MB.</small>
    </div>
</div>
<div class="form-actions"><a class="btn btn-ghost" href="{{ route('admin.kandidat.index') }}">Batal</a><button class="btn btn-primary" type="submit">{{ $submitLabel }}</button></div>
