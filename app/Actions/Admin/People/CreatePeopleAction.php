<?php

declare(strict_types=1);

namespace App\Actions\Admin\People;

use App\Models\People;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

final class CreatePeopleAction
{
    /**
     * Execute the action to create a new people.
     *
     * @param  array<string, mixed>  $peopleData
     */
    public function execute(array $peopleData, ?UploadedFile $avatarFile = null): People
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
            return People::create($peopleData);
        });
    }
}
