<?php

declare(strict_types=1);

namespace App\Http\Controllers\Staff;

use App\Actions\Staff\Profile\UpdateProfileAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\Profile\UpdateProfileRequest;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

final class ProfileController extends Controller
{
    public function __construct(
        private readonly UpdateProfileAction $updateProfileAction,
    ) {}

    /**
     * Show the profile edit form.
     */
    public function edit(): Response
    {
        return Inertia::render('Staff/Profile/Edit', [
            'staff' => auth('staff')->user(),
        ]);
    }

    /**
     * Update the authenticated staff's profile.
     */
    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        try {
            $staff = auth('staff')->user();

            $this->updateProfileAction->execute(
                staff: $staff,
                profileData: $request->validated(),
                avatarFile: $request->file('avatar')
            );

            return redirect()
                ->route('staff.profile.edit')
                ->with('success', 'Profil berhasil diperbarui!');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->withErrors(['error' => 'Gagal memperbarui profil: '.$e->getMessage()]);
        }
    }
}
