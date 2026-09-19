<?php

namespace App\Policies;

use App\Models\RoutineItem;
use App\Models\User;

class RoutineItemPolicy
{
    /**
     * Determine whether the user can view any routine items.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the routine item.
     */
    public function view(User $user, RoutineItem $routineItem): bool
    {
        return $user->id === $routineItem->user_id;
    }

    /**
     * Determine whether the user can create routine items.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the routine item.
     */
    public function update(User $user, RoutineItem $routineItem): bool
    {
        return $user->id === $routineItem->user_id;
    }

    /**
     * Determine whether the user can delete the routine item.
     */
    public function delete(User $user, RoutineItem $routineItem): bool
    {
        return $user->id === $routineItem->user_id;
    }
}
