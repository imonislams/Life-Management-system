<?php

namespace App\Policies;

use App\Models\RoutineOccurrence;
use App\Models\User;

class RoutineOccurrencePolicy
{
    public function view(User $user, RoutineOccurrence $occurrence): bool
    {
        return $user->id === $occurrence->user_id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, RoutineOccurrence $occurrence): bool
    {
        return $user->id === $occurrence->user_id;
    }

    public function delete(User $user, RoutineOccurrence $occurrence): bool
    {
        return $user->id === $occurrence->user_id;
    }
}
