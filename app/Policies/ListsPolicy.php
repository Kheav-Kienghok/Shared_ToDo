<?php

namespace App\Policies;

use App\Models\Lists;
use App\Models\User;

class ListsPolicy
{
    /**
     * Can view any lists? (e.g., list all lists)
     */
    public function viewAny(User $user): bool
    {
        return true; // user can list their own resources
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Lists $list): bool
    {
        $role = $this->getUserRole($user, $list);
        return in_array($role, ["owner", "collaborator", "viewer"]);
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
        $role = $this->getUserRole($user, $list);
        return in_array($role, ["owner", "editor"]);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Lists $list): bool
    {
        return $this->getUserRole($user, $list) === "owner";
    }

    /**
     * Can share the list / manage users? (only owner)
     */
    public function share(User $user, Lists $list): bool
    {
        return $this->getUserRole($user, $list) === "owner";
    }

    protected function getUserRole(User $user, Lists $list): ?string
    {
        return $list
            ->users()
            ->where("user_id", $user->id)
            ->first()?->pivot->role;
    }
}
