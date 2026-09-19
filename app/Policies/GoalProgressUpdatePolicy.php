<?php

namespace App\Policies;

use App\Models\GoalProgressUpdate;
use App\Models\User;

class GoalProgressUpdatePolicy
{
    public function view(User $user, GoalProgressUpdate $update): bool
    {
        return $user->id === $update->user_id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, GoalProgressUpdate $update): bool
    {
        return $user->id === $update->user_id;
    }

    public function delete(User $user, GoalProgressUpdate $update): bool
    {
        return $user->id === $update->user_id;
    }
}
