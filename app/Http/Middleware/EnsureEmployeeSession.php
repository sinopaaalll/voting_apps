<?php

namespace App\Http\Middleware;

use App\Models\Employee;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureEmployeeSession
{
    public function handle(Request $request, Closure $next): Response
    {
        $employee = Employee::find($request->session()->get('employee_id'));

        if (! $employee) {
            $request->session()->forget('employee_id');

            return redirect()->route('employee.login')
                ->withErrors(['nik' => 'Silakan masuk menggunakan NIK terlebih dahulu.']);
        }

        $request->attributes->set('employee', $employee);

        return $next($request);
    }
}
