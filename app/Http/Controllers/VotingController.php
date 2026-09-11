<?php

namespace App\Http\Controllers;

use App\Models\Kandidat;
use App\Models\Voting;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class VotingController extends Controller
{
    public function index(Request $request): View
    {
        $employee = $request->attributes->get('employee');
        $hasVoted = $employee->voting()->exists();
        $kandidat = $hasVoted
            ? collect()
            : Kandidat::query()->orderBy('nomor_urut')->get();

        return view('voting.index', compact('employee', 'hasVoted', 'kandidat'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'kandidat_id' => ['required', 'integer', 'exists:kandidat,id'],
            'confirmation' => ['accepted'],
        ], [
            'kandidat_id.required' => 'Silakan pilih kandidat.',
            'kandidat_id.exists' => 'Kandidat yang dipilih tidak tersedia.',
            'confirmation.accepted' => 'Anda harus mengonfirmasi pilihan terlebih dahulu.',
        ]);

        $employee = $request->attributes->get('employee');

        try {
            DB::transaction(function () use ($employee, $validated): void {
                Voting::create([
                    'employee_id' => $employee->id,
                    'kandidat_id' => $validated['kandidat_id'],
                ]);
            });
        } catch (UniqueConstraintViolationException) {
            return redirect()->route('voting.index')
                ->withErrors(['vote' => 'Anda sudah memberikan suara. Setiap employee hanya dapat memilih satu kali.']);
        }

        return redirect()->route('voting.index')
            ->with('success', 'Suara Anda berhasil disimpan secara permanen. Terima kasih telah berpartisipasi.');
    }
}
