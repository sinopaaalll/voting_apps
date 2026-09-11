<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Kandidat;
use App\Models\Voting;
use Illuminate\View\View;

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
