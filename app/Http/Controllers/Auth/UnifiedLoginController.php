<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Traits\ClearsOtherGuards;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

/**
 * Unified Login Controller untuk auto-detect user type.
 * Menangani login untuk Admin (web guard) dan Staff (staff guard).
 */
final class UnifiedLoginController extends Controller
{
    use ClearsOtherGuards;

    /**
     * Proses unified login dengan auto-detection user type.
     *
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        $email = $request->input('email');
        $password = $request->input('password');
        $remember = $request->boolean('remember');

        // Clear all guards BEFORE attempting login
        $this->clearAllGuards();

        // Coba login sebagai Admin (web guard) terlebih dahulu
        if ($this->attemptAdminLogin($email, $password, $remember)) {
            $request->session()->regenerate();

            return redirect()->intended(route('dashboard'));
        }

        // Jika bukan admin, coba login sebagai Staff
        if ($this->attemptStaffLogin($email, $password, $remember)) {
            $request->session()->regenerate();

            return redirect()->intended(route('staff.profile.edit'));
        }

        // Jika kedua attempt gagal, throw validation error
        throw ValidationException::withMessages([
            'email' => __('Email atau password yang Anda masukkan salah.'),
        ]);
    }

    /**
     * Attempt login sebagai Admin.
     */
    protected function attemptAdminLogin(string $email, string $password, bool $remember): bool
    {
        // Cek apakah user ada di tabel users
        $user = User::where('email', $email)->first();

        if (! $user) {
            return false;
        }

        return Auth::guard('web')->attempt([
            'email' => $email,
            'password' => $password,
        ], $remember);
    }

    /**
     * Attempt login sebagai Staff.
     */
    protected function attemptStaffLogin(string $email, string $password, bool $remember): bool
    {
        // Cek apakah user ada di tabel staffs
        $staff = Staff::where('email', $email)->first();

        if (! $staff) {
            return false;
        }

        // Cek apakah staff aktif
        if (! $staff->is_active) {
            return false;
        }

        return Auth::guard('staff')->attempt([
            'email' => $email,
            'password' => $password,
        ], $remember);
    }
}
