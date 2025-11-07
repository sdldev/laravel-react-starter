<?php

declare(strict_types=1);

namespace App\Http\Controllers\Peoples;

use App\Actions\Peoples\Profile\UpdateProfileAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Peoples\Profile\UpdateProfileRequest;
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
        return Inertia::render('Peoples/Profile/Edit', [
            'people' => auth('peoples')->user(),
        ]);
    }

    /**
     * Update the authenticated people's profile.
     */
    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        try {
            $people = auth('peoples')->user();

            $this->updateProfileAction->execute(
                people: $people,
                profileData: $request->validated(),
                avatarFile: $request->file('avatar')
            );

            return redirect()
                ->route('peoples.profile.edit')
                ->with('success', 'Profil berhasil diperbarui!');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->withErrors(['error' => 'Gagal memperbarui profil: '.$e->getMessage()]);
        }
    }
}
