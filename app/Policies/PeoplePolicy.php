<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\People;
use App\Models\User;

final class PeoplePolicy
{
    /**
     * Determine whether the admin can view any people.
     */
    public function viewAny(User $user): bool
    {
        // Only admins can view all people
        return true;
    }

    /**
     * Determine whether the admin can view the people.
     */
    public function view(User $user, People $people): bool
    {
        // Only admins can view individual people details
        return true;
    }

    /**
     * Determine whether the admin can create people.
     */
    public function create(User $user): bool
    {
        // Only admins can create new people
        return true;
    }

    /**
     * Determine whether the admin can update the people.
     */
    public function update(User $user, People $people): bool
    {
        // Only admins can update people
        return true;
    }

    /**
     * Determine whether the admin can delete the people.
     */
    public function delete(User $user, People $people): bool
    {
        // Only admins can delete people
        return true;
    }

    /**
     * Determine whether the admin can restore the people.
     */
    public function restore(User $user, People $people): bool
    {
        // Only admins can restore people
        return true;
    }

    /**
     * Determine whether the admin can permanently delete the people.
     */
    public function forceDelete(User $user, People $people): bool
    {
        // Only admins can force delete people
        return true;
    }
}
