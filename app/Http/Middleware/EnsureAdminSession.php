<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminSession
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->session()->get('admin_authenticated', false)) {
            return redirect()->route('admin.login')
                ->withErrors(['username' => 'Silakan masuk sebagai admin terlebih dahulu.']);
        }

        return $next($request);
    }
}
