<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;

class AdminAuthController extends Controller
{
    public function create(Request $request): View|RedirectResponse
    {
        if ($request->session()->get('admin_authenticated')) {
            return redirect()->route('admin.dashboard');
        }

        return view('auth.admin-login');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'username' => ['required', 'string', 'max:100'],
            'password' => ['required', 'string'],
        ], [
            'username.required' => 'Username wajib diisi.',
            'password.required' => 'Password wajib diisi.',
        ]);

        $key = 'admin-login:'.$request->ip().':'.mb_strtolower($validated['username']);

        if (RateLimiter::tooManyAttempts($key, 5)) {
            return back()->withInput($request->only('username'))->withErrors([
                'username' => 'Terlalu banyak percobaan. Coba lagi dalam '.RateLimiter::availableIn($key).' detik.',
            ]);
        }

        $usernameMatches = hash_equals((string) config('voting.admin.username'), $validated['username']);
        $passwordHash = (string) config('voting.admin.password_hash');
        $passwordMatches = $passwordHash !== '' && Hash::check($validated['password'], $passwordHash);

        if (! $usernameMatches || ! $passwordMatches) {
            RateLimiter::hit($key, 60);

            return back()->withInput($request->only('username'))
                ->withErrors(['username' => 'Username atau password salah.']);
        }

        RateLimiter::clear($key);
        $request->session()->regenerate();
        $request->session()->put('admin_authenticated', true);

        return redirect()->intended(route('admin.dashboard'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login')->with('success', 'Sesi admin telah berakhir.');
    }
}
