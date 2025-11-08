<?php

declare(strict_types=1);

use App\Http\Controllers\Admin\StaffController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
|
| Here is where you can register admin routes for your application.
| These routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group with "auth:web" guard.
|
*/

Route::middleware(['auth:web', 'verified'])->prefix('admin')->name('admin.')->group(function (): void {
    // Staff Management Routes (Admin CRUD)
    Route::resource('staff', StaffController::class);
});
