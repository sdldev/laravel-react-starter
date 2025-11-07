<?php

declare(strict_types=1);

namespace App\Actions\Peoples\Profile;

use App\Models\People;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

final class UpdateProfileAction
{
    /**
     * Execute the action to update people profile.
     *
     * @param  array<string, mixed>  $profileData
     */
    public function execute(People $people, array $profileData, ?UploadedFile $avatarFile = null): People
    {
        return DB::transaction(function () use ($people, $profileData, $avatarFile) {
            // Handle avatar upload
            if ($avatarFile !== null) {
                // Delete old avatar if exists
                if ($people->avatar !== null) {
                    Storage::disk('public')->delete($people->avatar);
                }

                $profileData['avatar'] = $avatarFile->store('avatars/peoples', 'public');
            }

            // Update profile (excluding password and sensitive fields)
            $people->update($profileData);

            return $people->fresh();
        });
    }
}
