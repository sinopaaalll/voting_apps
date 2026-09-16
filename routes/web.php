<?php

use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AdminEmployeeController;
use App\Http\Controllers\AdminKandidatController;
use App\Http\Controllers\EmployeeAuthController;
use App\Http\Controllers\VotingController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');

Route::get('/login', [EmployeeAuthController::class, 'create'])->name('employee.login');
Route::post('/login', [EmployeeAuthController::class, 'store'])->name('employee.login.store');

Route::middleware('employee.session')->group(function (): void {
    Route::get('/voting', [VotingController::class, 'index'])->name('voting.index');
    Route::post('/voting', [VotingController::class, 'store'])->name('voting.store');
    Route::post('/logout', [EmployeeAuthController::class, 'destroy'])->name('employee.logout');
});

Route::get('/admin/login', [AdminAuthController::class, 'create'])->name('admin.login');
Route::post('/admin/login', [AdminAuthController::class, 'store'])->name('admin.login.store');

Route::prefix('admin')->name('admin.')->middleware('admin.session')->group(function (): void {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/hasil', [AdminDashboardController::class, 'results'])->name('results');
    Route::get('/hasil/export', [AdminDashboardController::class, 'exportResults'])->name('results.export');
    Route::get('/hasil/{kandidat}/export', [AdminDashboardController::class, 'exportCandidateResults'])->whereNumber('kandidat')->name('results.candidate-export');
    Route::patch('/hasil/voting/{voting}/kandidat', [AdminDashboardController::class, 'moveVote'])->whereNumber('voting')->name('results.vote.move');
    Route::get('/hasil/{kandidat}', [AdminDashboardController::class, 'showResult'])->whereNumber('kandidat')->name('results.show');
    Route::get('/employees/template-import', [AdminEmployeeController::class, 'template'])->name('employees.template');
    Route::post('/employees/import', [AdminEmployeeController::class, 'import'])->name('employees.import');
    Route::delete('/employees/bulk', [AdminEmployeeController::class, 'bulkDestroy'])->name('employees.bulk-destroy');
    Route::resource('employees', AdminEmployeeController::class)->except('show');
    Route::resource('kandidat', AdminKandidatController::class)->except('show');
    Route::post('/logout', [AdminAuthController::class, 'destroy'])->name('logout');
});
