<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CharacterSheet extends Model
{
    public const string STATUS_DRAFT = 'draft';

    public const string STATUS_COMPLETED = 'completed';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'status',
        'state',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'state' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Get player combat stats (hp, defense, damage) from sheet state for use in game session.
     *
     * @return array{hp: int, defense: int, damage: int}
     */
    public function toCombatPlayerState(): array
    {
        $state = $this->state ?? [];
        $combat = $state['combat'] ?? [];

        $hp = (int) ($combat['hp_current'] ?? $combat['hp_max'] ?? 30);
        $defense = (int) ($combat['ac'] ?? 10);
        $damage = (int) ($combat['damage_bonus'] ?? 5);

        if ($hp <= 0 && isset($combat['hp_max'])) {
            $hp = (int) $combat['hp_max'];
        }

        return [
            'hp' => max(1, $hp),
            'defense' => max(1, min(30, $defense)),
            'damage' => max(1, min(100, $damage)),
        ];
    }
}
