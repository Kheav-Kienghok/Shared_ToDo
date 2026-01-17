<?php

namespace App\Policies;

use App\Models\Lists;
use App\Models\User;

class ListsPolicy
{
    public function viewAny(User $user): bool
    {
        return true; // user can list their own resources
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Lists $list): bool
    {
        return $user->id === $list->owner_id;
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
    public function update(User $user, Lists $list): bool
    {
        return $user->id === $list->owner_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Lists $list): bool
    {
        return $user->id === $list->owner_id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Lists $list): bool
    {
        return $user->id === $list->owner_id;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Lists $list): bool
    {
        return $user->id === $list->owner_id;
    }
}
