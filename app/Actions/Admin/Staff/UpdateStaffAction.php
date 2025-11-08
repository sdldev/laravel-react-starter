<?php

declare(strict_types=1);

namespace App\Actions\Admin\Staff;

use App\Models\Staff;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

final class UpdateStaffAction
{
    /**
     * Execute the action to update an existing staff.
     *
     * @param  array<string, mixed>  $staffData
     */
    public function execute(Staff $staff, array $staffData, ?UploadedFile $avatarFile = null): Staff
    {
        return DB::transaction(function () use ($staff, $staffData, $avatarFile) {
            // Hash password if provided and not empty
            if (isset($staffData['password']) && ! empty($staffData['password'])) {
                $staffData['password'] = Hash::make($staffData['password']);
            } else {
                // Remove password from update data if empty
                unset($staffData['password']);
            }

            // Handle avatar upload
            if ($avatarFile !== null) {
                // Delete old avatar if exists
                if ($staff->avatar !== null) {
                    Storage::disk('public')->delete($staff->avatar);
                }

                $staffData['avatar'] = $avatarFile->store('avatars/staff', 'public');
            }

            // Update staff record
            $staff->update($staffData);

            return $staff->fresh();
        });
    }
}
