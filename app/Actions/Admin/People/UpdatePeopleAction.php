<?php

declare(strict_types=1);

namespace App\Actions\Admin\People;

use App\Models\People;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

final class UpdatePeopleAction
{
    /**
     * Execute the action to update an existing people.
     *
     * @param  array<string, mixed>  $peopleData
     */
    public function execute(People $people, array $peopleData, ?UploadedFile $avatarFile = null): People
    {
        return DB::transaction(function () use ($people, $peopleData, $avatarFile) {
            // Hash password if provided and not empty
            if (isset($peopleData['password']) && ! empty($peopleData['password'])) {
                $peopleData['password'] = Hash::make($peopleData['password']);
            } else {
                // Remove password from update data if empty
                unset($peopleData['password']);
            }

            // Handle avatar upload
            if ($avatarFile !== null) {
                // Delete old avatar if exists
                if ($people->avatar !== null) {
                    Storage::disk('public')->delete($people->avatar);
                }

                $peopleData['avatar'] = $avatarFile->store('avatars/peoples', 'public');
            }

            // Update people record
            $people->update($peopleData);

            return $people->fresh();
        });
    }
}
