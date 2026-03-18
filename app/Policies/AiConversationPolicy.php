<?php

namespace App\Policies;

use App\Models\AiConversation;
use App\Models\User;

class AiConversationPolicy
{
    /**
     * Determine if the user can view the conversation.
     */
    public function view(User $user, AiConversation $conversation): bool
    {
        return $conversation->user_id === $user->id
            || $user->isAdmin()
            || $user->isSupervisor()
            || ($user->isCosupervisor() && $conversation->student?->cosupervisor_id === $user->id);
    }

    /**
     * Determine if the user can update the conversation.
     */
    public function update(User $user, AiConversation $conversation): bool
    {
        return $conversation->user_id === $user->id
            || $user->isAdmin();
    }

    /**
     * Determine if the user can delete the conversation.
     */
    public function delete(User $user, AiConversation $conversation): bool
    {
        return $conversation->user_id === $user->id
            || $user->isAdmin();
    }

    /**
     * Determine if the user can create conversations.
     */
    public function create(User $user): bool
    {
        return true; // All authenticated users can create conversations
    }
}
