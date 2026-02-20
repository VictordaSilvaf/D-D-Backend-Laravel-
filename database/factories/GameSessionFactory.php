<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\GameSession>
 */
class GameSessionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'state' => [
                'player' => [
                    'hp' => 30,
                    'defense' => 10,
                    'damage' => 5,
                ],
                'enemy' => [
                    'hp' => fake()->numberBetween(20, 100),
                    'defense' => fake()->numberBetween(5, 20),
                    'damage' => fake()->numberBetween(5, 30),
                ],
                'combat_log' => [],
            ],
            'combat_ended' => false,
        ];
    }

    /**
     * Indicate that the combat has ended.
     */
    public function ended(): static
    {
        return $this->state(fn(array $attributes) => [
            'combat_ended' => true,
        ]);
    }

    /**
     * Indicate that the player has won.
     */
    public function playerWon(): static
    {
        return $this->state(fn(array $attributes) => [
            'combat_ended' => true,
            'state' => [
                'player' => [
                    'hp' => fake()->numberBetween(1, 30),
                    'defense' => 10,
                    'damage' => 5,
                ],
                'enemy' => [
                    'hp' => 0,
                    'defense' => $attributes['state']['enemy']['defense'],
                    'damage' => $attributes['state']['enemy']['damage'],
                ],
                'combat_log' => [
                    'O combate foi encerrado. Jogador venceu!',
                ],
            ],
        ]);
    }

    /**
     * Indicate that the player has lost.
     */
    public function playerLost(): static
    {
        return $this->state(fn(array $attributes) => [
            'combat_ended' => true,
            'state' => [
                'player' => [
                    'hp' => 0,
                    'defense' => 10,
                    'damage' => 5,
                ],
                'enemy' => [
                    'hp' => fake()->numberBetween(1, 100),
                    'defense' => $attributes['state']['enemy']['defense'],
                    'damage' => $attributes['state']['enemy']['damage'],
                ],
                'combat_log' => [
                    'O combate foi encerrado. Jogador perdeu!',
                ],
            ],
        ]);
    }
}
