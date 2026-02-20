<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\GameSession;
use App\Models\User;

class GameSessionPolicy
{
    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, GameSession $gameSession): bool
    {
        return $user->id === $gameSession->user_id;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, GameSession $gameSession): bool
    {
        return $user->id === $gameSession->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, GameSession $gameSession): bool
    {
        return $user->id === $gameSession->user_id;
    }
}
