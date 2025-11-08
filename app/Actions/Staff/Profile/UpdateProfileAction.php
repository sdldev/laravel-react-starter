<?php

declare(strict_types=1);

namespace App\Actions\Staff\Profile;

use App\Models\Staff;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

final class UpdateProfileAction
{
    /**
     * Execute the action to update staff profile.
     *
     * @param  array<string, mixed>  $profileData
     */
    public function execute(Staff $staff, array $profileData, ?UploadedFile $avatarFile = null): Staff
    {
        return DB::transaction(function () use ($staff, $profileData, $avatarFile) {
            // Handle avatar upload
            if ($avatarFile !== null) {
                // Delete old avatar if exists
                if ($staff->avatar !== null) {
                    Storage::disk('public')->delete($staff->avatar);
                }

                $profileData['avatar'] = $avatarFile->store('avatars/staff', 'public');
            }

            // Update profile (excluding password and sensitive fields)
            $staff->update($profileData);

            return $staff->fresh();
        });
    }
}
