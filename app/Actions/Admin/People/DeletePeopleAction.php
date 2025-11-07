<?php

declare(strict_types=1);

namespace App\Actions\Admin\People;

use App\Models\People;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

final class DeletePeopleAction
{
    /**
     * Execute the action to delete a people.
     */
    public function execute(People $people): bool
    {
        return DB::transaction(function () use ($people) {
            // Delete avatar if exists
            if ($people->avatar !== null) {
                Storage::disk('public')->delete($people->avatar);
            }

            // Delete people record
            return $people->delete();
        });
    }
}
