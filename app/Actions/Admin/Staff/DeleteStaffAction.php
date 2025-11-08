<?php

declare(strict_types=1);

namespace App\Actions\Admin\Staff;

use App\Models\Staff;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

final class DeleteStaffAction
{
    /**
     * Execute the action to delete a staff.
     */
    public function execute(Staff $staff): bool
    {
        return DB::transaction(function () use ($staff) {
            // Delete avatar if exists
            if ($staff->avatar !== null) {
                Storage::disk('public')->delete($staff->avatar);
            }

            // Delete staff record
            return $staff->delete();
        });
    }
}
