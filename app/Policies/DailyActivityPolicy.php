<?php

namespace App\Policies;

use App\Models\DailyActivity;
use App\Models\User;

class DailyActivityPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, DailyActivity $activity): bool
    {
        return $user->id === $activity->user_id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, DailyActivity $activity): bool
    {
        return $user->id === $activity->user_id;
    }

    public function delete(User $user, DailyActivity $activity): bool
    {
        return $user->id === $activity->user_id;
    }
}
