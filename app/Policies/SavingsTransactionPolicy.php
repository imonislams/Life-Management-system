<?php

namespace App\Policies;

use App\Models\SavingsTransaction;
use App\Models\User;

class SavingsTransactionPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, SavingsTransaction $transaction): bool
    {
        return $user->id === $transaction->user_id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, SavingsTransaction $transaction): bool
    {
        return $user->id === $transaction->user_id;
    }

    public function delete(User $user, SavingsTransaction $transaction): bool
    {
        return $user->id === $transaction->user_id;
    }
}
