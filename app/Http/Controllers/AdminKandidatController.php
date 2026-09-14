<?php

namespace App\Http\Controllers;

use App\Models\Kandidat;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminKandidatController extends Controller
{
    public function index(): View
    {
        $kandidat = Kandidat::query()->withCount('votes')->orderBy('nomor_urut')->get();

        return view('admin.kandidat.index', compact('kandidat'));
    }

    public function create(): View
    {
        return view('admin.kandidat.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedData($request);
        $data['photo'] = $request->file('photo')->store('kandidat', 'public');
        Kandidat::create($data);

        return redirect()->route('admin.kandidat.index')->with('success', 'Kandidat berhasil ditambahkan.');
    }

    public function edit(Kandidat $kandidat): View|RedirectResponse
    {
        if ($kandidat->votes()->exists()) {
            return redirect()->route('admin.kandidat.index')
                ->withErrors(['edit' => 'Kandidat yang sudah memperoleh suara tidak dapat diedit.']);
        }

        return view('admin.kandidat.edit', compact('kandidat'));
    }

    public function update(Request $request, Kandidat $kandidat): RedirectResponse
    {
        if ($kandidat->votes()->exists()) {
            return redirect()->route('admin.kandidat.index')
                ->withErrors(['edit' => 'Kandidat yang sudah memperoleh suara tidak dapat diedit.']);
        }

        $data = $this->validatedData($request, $kandidat);

        if ($request->hasFile('photo')) {
            $oldPhoto = $kandidat->photo;
            $data['photo'] = $request->file('photo')->store('kandidat', 'public');
            $kandidat->update($data);
            Storage::disk('public')->delete($oldPhoto);
        } else {
            $kandidat->update($data);
        }

        return redirect()->route('admin.kandidat.index')->with('success', 'Kandidat berhasil diperbarui.');
    }

    public function destroy(Kandidat $kandidat): RedirectResponse
    {
        $photo = $kandidat->photo;
        $deletedVotes = $kandidat->votes()->count();

        DB::transaction(function () use ($kandidat): void {
            $kandidat->delete();
        });

        Storage::disk('public')->delete($photo);

        return back()->with(
            'success',
            "Kandidat berhasil dihapus bersama {$deletedVotes} suara terkait.",
        );
    }

    private function validatedData(Request $request, ?Kandidat $kandidat = null): array
    {
        return $request->validate([
            'nomor_urut' => [
                'required',
                'integer',
                'min:1',
                'max:999',
                Rule::unique('kandidat', 'nomor_urut')->ignore($kandidat?->id),
            ],
            'name' => ['required', 'string', 'max:100'],
            'photo' => [$kandidat ? 'nullable' : 'required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [
            'nomor_urut.required' => 'Nomor urut wajib diisi.',
            'nomor_urut.integer' => 'Nomor urut harus berupa angka.',
            'nomor_urut.unique' => 'Nomor urut sudah digunakan.',
            'name.required' => 'Nama kandidat wajib diisi.',
            'photo.required' => 'Foto kandidat wajib diunggah.',
            'photo.image' => 'File harus berupa gambar.',
            'photo.mimes' => 'Foto harus berformat JPG, PNG, atau WebP.',
            'photo.max' => 'Ukuran foto maksimal 2 MB.',
        ]);
    }
}
