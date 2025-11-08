<?php

declare(strict_types=1);

namespace App\Actions\Admin\Staff;

use App\Models\Staff;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

final class CreateStaffAction
{
    /**
     * Execute the action to create a new people.
     *
     * @param  array<string, mixed>  $peopleData
     */
    public function execute(array $peopleData, ?UploadedFile $avatarFile = null): Staff
    {
        return DB::transaction(function () use ($peopleData, $avatarFile) {
            // Hash password if provided
            if (isset($peopleData['password'])) {
                $peopleData['password'] = Hash::make($peopleData['password']);
            }

            // Handle avatar upload
            if ($avatarFile !== null) {
                $peopleData['avatar'] = $avatarFile->store('avatars/peoples', 'public');
            }

            // Create people record
            return Staff::create($peopleData);
        });
    }
}
