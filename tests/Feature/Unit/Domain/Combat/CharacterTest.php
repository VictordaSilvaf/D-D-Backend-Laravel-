<?php

declare(strict_types=1);

use App\Domain\Combat\Character;

it('reduz hp ao receber dano', function () {

    $character = new Character(hp: 20, defense: 10, damage: 5);

    $character->takeDamage(5);

    expect($character->hp)->toBe(15);
});

it('hp nunca fica negativo', function () {

    $character = new Character(hp: 10, defense: 10, damage: 5);

    $character->takeDamage(50);

    expect($character->hp)->toBe(0);
});

it('retorna false quando hp é zero', function () {

    $character = new Character(hp: 0, defense: 10, damage: 5);

    expect($character->isAlive())->toBeFalse();
});
