<?php

namespace App\Policies;

use App\Models\Milieu;
use App\Models\User;

class MilieuPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Milieu $milieu): bool
    {
        return $milieu->canView($user);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Milieu $milieu): bool
    {
        return $milieu->canEdit($user);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Milieu $milieu): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Milieu $milieu): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Milieu $milieu): bool
    {
        return false;
    }
}
