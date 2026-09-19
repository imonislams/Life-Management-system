<?php

namespace App\Policies;

use App\Models\HabitActivity;
use App\Models\User;

class HabitActivityPolicy
{
    public function view(User $user, HabitActivity $activity): bool
    {
        return $user->id === $activity->user_id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, HabitActivity $activity): bool
    {
        return $user->id === $activity->user_id;
    }

    public function delete(User $user, HabitActivity $activity): bool
    {
        return $user->id === $activity->user_id;
    }
}
