<?php

declare(strict_types=1);

use App\Http\Controllers\Staff\ProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Staff Routes
|--------------------------------------------------------------------------
|
| Here is where you can register staff routes for your application.
| These routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "staff" middleware group with "auth:staff" guard.
|
*/

Route::middleware(['auth:staff', 'verified'])->prefix('staff')->name('staff.')->group(function (): void {
    // Profile Management Routes (Staff can update their own profile)
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
});
