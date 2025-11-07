<?php

declare(strict_types=1);

use App\Http\Controllers\Peoples\ProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Peoples Routes
|--------------------------------------------------------------------------
|
| Here is where you can register peoples routes for your application.
| These routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "peoples" middleware group with "auth:peoples" guard.
|
*/

Route::middleware(['auth:peoples', 'verified'])->prefix('peoples')->name('peoples.')->group(function (): void {
    // Profile Management Routes (Peoples can update their own profile)
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
});
