<?php

declare(strict_types=1);

namespace App\Http\Traits;

use Illuminate\Support\Facades\Auth;

/**
 * Trait untuk membersihkan session guard lain saat login.
 * Mencegah user login di multiple guards secara bersamaan.
 */
trait ClearsOtherGuards
{
    /**
     * Logout dari semua guards kecuali yang ditentukan.
     *
     * @param  string  $except  Guard yang tidak akan di-logout
     * @return void
     */
    protected function clearAllGuardsExcept(string $except): void
    {
        $guards = array_keys(config('auth.guards'));

        foreach ($guards as $guard) {
            if ($guard !== $except && Auth::guard($guard)->check()) {
                Auth::guard($guard)->logout();
            }
        }
    }

    /**
     * Logout dari semua guards.
     *
     * @return void
     */
    protected function clearAllGuards(): void
    {
        $guards = array_keys(config('auth.guards'));

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                Auth::guard($guard)->logout();
            }
        }
    }
}
