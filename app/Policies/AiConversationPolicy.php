<?php

namespace App\Policies;

use App\Models\AiConversation;
use App\Models\User;

/**
 * Ownership policy for AI conversations.
 *
 * A user may only ever view, continue or delete their OWN conversations. This is
 * the authorization half of the privacy guarantee; the retrieval half is enforced
 * by user-scoped vector search.
 */
class AiConversationPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, AiConversation $conversation): bool
    {
        return $conversation->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, AiConversation $conversation): bool
    {
        return $conversation->user_id === $user->id;
    }

    public function delete(User $user, AiConversation $conversation): bool
    {
        return $conversation->user_id === $user->id;
    }
}
