<?php

namespace App\Policies;

use App\Models\Currency;
use App\Models\User;

class CurrencyPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Currency $currency): bool
    {
        return $user->id === $currency->user_id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Currency $currency): bool
    {
        return $user->id === $currency->user_id;
    }

    public function delete(User $user, Currency $currency): bool
    {
        return $user->id === $currency->user_id;
    }

    /**
     * Activate / deactivate.
     */
    public function toggle(User $user, Currency $currency): bool
    {
        return $user->id === $currency->user_id;
    }

    /**
     * Promote to the single default currency.
     */
    public function makeDefault(User $user, Currency $currency): bool
    {
        return $user->id === $currency->user_id;
    }
}
