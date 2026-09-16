<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Kandidat;
use App\Models\Voting;
use App\Services\VotingResultsExporter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminDashboardController extends Controller
{
    public function index(): View
    {
        return view('admin.dashboard', $this->resultData());
    }

    public function results(): View
    {
        return view('admin.results', $this->resultData());
    }

    public function showResult(Request $request, Kandidat $kandidat): View
    {
        $totalVotes = $kandidat->votes()->count();
        $votes = $kandidat->votes()
            ->with('employee')
            ->when($request->filled('q'), function ($query) use ($request): void {
                $search = $request->string('q')->trim()->value();
                $query->whereHas('employee', function ($query) use ($search): void {
                    $query->where('nik', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(30)
            ->withQueryString();
        $targetCandidates = Kandidat::query()
            ->whereKeyNot($kandidat->id)
            ->orderBy('nomor_urut')
            ->get(['id', 'nomor_urut', 'name']);

        return view('admin.result-detail', compact('kandidat', 'votes', 'totalVotes', 'targetCandidates'));
    }

    public function exportResults(VotingResultsExporter $exporter): StreamedResponse
    {
        return $exporter->download();
    }

    public function exportCandidateResults(Kandidat $kandidat, VotingResultsExporter $exporter): StreamedResponse
    {
        return $exporter->downloadCandidate($kandidat);
    }

    public function moveVote(Request $request, Voting $voting): RedirectResponse
    {
        $validated = $request->validate([
            'kandidat_id' => [
                'required',
                'integer',
                'exists:kandidat,id',
                Rule::notIn([$voting->kandidat_id]),
            ],
        ], [
            'kandidat_id.required' => 'Pilih kandidat tujuan.',
            'kandidat_id.exists' => 'Kandidat tujuan tidak tersedia.',
            'kandidat_id.not_in' => 'Pilih kandidat tujuan yang berbeda.',
        ]);

        $result = DB::transaction(function () use ($voting, $validated): array {
            $lockedVote = Voting::query()
                ->with(['employee', 'kandidat'])
                ->lockForUpdate()
                ->findOrFail($voting->id);
            $targetCandidate = Kandidat::query()
                ->lockForUpdate()
                ->findOrFail($validated['kandidat_id']);

            if ($lockedVote->kandidat_id === $targetCandidate->id) {
                throw ValidationException::withMessages([
                    'kandidat_id' => 'Pilih kandidat tujuan yang berbeda.',
                ]);
            }

            $result = [
                'employee' => $lockedVote->employee->name,
                'source' => $lockedVote->kandidat->name,
                'target' => $targetCandidate->name,
            ];

            $lockedVote->update(['kandidat_id' => $targetCandidate->id]);

            return $result;
        });

        return back()->with(
            'success',
            "Suara {$result['employee']} berhasil dipindahkan dari {$result['source']} ke {$result['target']}.",
        );
    }

    private function resultData(): array
    {
        $totalEmployee = Employee::count();
        $totalVotes = Voting::count();
        $kandidat = Kandidat::query()->withCount('votes')->orderBy('nomor_urut')->get();

        return [
            'totalEmployee' => $totalEmployee,
            'totalVotes' => $totalVotes,
            'notVoted' => max(0, $totalEmployee - $totalVotes),
            'participation' => $totalEmployee > 0 ? round(($totalVotes / $totalEmployee) * 100, 1) : 0,
            'kandidat' => $kandidat,
        ];
    }
}
