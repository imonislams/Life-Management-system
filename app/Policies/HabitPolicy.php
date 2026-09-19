<?php

namespace App\Policies;

use App\Models\Habit;
use App\Models\User;

class HabitPolicy
{
    /**
     * Determine whether the user can view any habits.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the habit.
     */
    public function view(User $user, Habit $habit): bool
    {
        return $user->id === $habit->user_id;
    }

    /**
     * Determine whether the user can create habits.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the habit.
     */
    public function update(User $user, Habit $habit): bool
    {
        return $user->id === $habit->user_id;
    }

    /**
     * Determine whether the user can delete the habit.
     */
    public function delete(User $user, Habit $habit): bool
    {
        return $user->id === $habit->user_id;
    }

    /**
     * Determine whether the user can toggle completion for the habit.
     */
    public function toggle(User $user, Habit $habit): bool
    {
        return $user->id === $habit->user_id;
    }
}
