<div class="form-grid">
    <label class="field"><span>Nomor urut</span><input type="number" name="nomor_urut" value="{{ old('nomor_urut', $kandidat->nomor_urut ?? '') }}" min="1" max="999" required></label>
    <label class="field"><span>Nama kandidat</span><input name="name" value="{{ old('name', $kandidat->name ?? '') }}" maxlength="100" required></label>
    <label class="field field-wide"><span>Foto kandidat {{ isset($kandidat) ? '(kosongkan jika tidak diubah)' : '' }}</span><input type="file" name="photo" accept="image/jpeg,image/png,image/webp" {{ isset($kandidat) ? '' : 'required' }}><small>JPG, PNG, atau WebP. Maksimal 2 MB.</small></label>
    <label class="field field-wide"><span>Visi dan misi</span><textarea name="visi_misi" rows="8" maxlength="5000" required>{{ old('visi_misi', $kandidat->visi_misi ?? '') }}</textarea></label>
</div>
<div class="form-actions"><a class="btn btn-ghost" href="{{ route('admin.kandidat.index') }}">Batal</a><button class="btn btn-primary" type="submit">{{ $submitLabel }}</button></div>
