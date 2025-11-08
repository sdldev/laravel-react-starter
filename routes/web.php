<?php

use App\Http\Controllers\Auth\UnifiedLoginController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return Inertia::render('welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

// Unified login route (alternative to default Fortify login)
Route::post('unified-login', [UnifiedLoginController::class, 'store'])
    ->middleware(['throttle:login'])
    ->name('unified.login');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        return Inertia::render('dashboard');
    })->name('dashboard');
});

require __DIR__.'/settings.php';
