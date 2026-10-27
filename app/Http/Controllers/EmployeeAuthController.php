<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\View\View;

class EmployeeAuthController extends Controller
{
    public function create(Request $request): View|RedirectResponse
    {
        if ($request->session()->has('employee_id')) {
            return redirect()->route('voting.index');
        }

        return view('auth.employee-login');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nik' => ['required', 'string', 'max:30'],
            'birth_date' => ['required', 'date_format:Y-m-d', 'before:today'],
        ], [
            'nik.required' => 'NIK wajib diisi.',
            'nik.max' => 'NIK maksimal 30 karakter.',
            'birth_date.required' => 'Tanggal lahir wajib dikonfirmasi.',
            'birth_date.date_format' => 'Tanggal lahir tidak valid.',
            'birth_date.before' => 'Tanggal lahir tidak valid.',
        ]);

        $nik = Str::upper(trim($validated['nik']));
        $key = 'employee-login:'.$request->ip().':'.$nik;

        if (RateLimiter::tooManyAttempts($key, 5)) {
            return back()->withInput()->withErrors([
                'nik' => 'Terlalu banyak percobaan. Coba lagi dalam '.RateLimiter::availableIn($key).' detik.',
            ]);
        }

        $employee = Employee::query()
            ->where('nik', $nik)
            ->whereDate('birth_date', $validated['birth_date'])
            ->first();

        if (! $employee) {
            RateLimiter::hit($key, 60);

            return back()->withInput()->withErrors(['nik' => 'NIK atau tanggal lahir tidak sesuai.']);
        }

        RateLimiter::clear($key);
        $request->session()->regenerate();
        $request->session()->put('employee_id', $employee->id);

        return redirect()->intended(route('voting.index'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('employee.login')->with('success', 'Anda berhasil keluar.');
    }
}
