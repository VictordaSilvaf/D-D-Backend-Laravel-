<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\CharacterSheet;
use App\Models\User;

class CharacterSheetPolicy
{
    public function view(User $user, CharacterSheet $characterSheet): bool
    {
        return $user->id === $characterSheet->user_id;
    }

    public function update(User $user, CharacterSheet $characterSheet): bool
    {
        return $user->id === $characterSheet->user_id;
    }

    public function delete(User $user, CharacterSheet $characterSheet): bool
    {
        return $user->id === $characterSheet->user_id;
    }
}
