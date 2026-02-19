<?php

namespace App\Providers;

use App\Domain\Combat\CombatResolver;
use App\Domain\Combat\Contracts\CombatResolver as CombatResolverContract;
use App\Domain\Combat\Contracts\DiceRoller as CombatDiceRollerContract;
use App\Domain\Combat\RandomDiceRoller;
use App\Domain\Turn\CombatResolverAdapter;
use App\Domain\Turn\CombatStateArrayMapper;
use App\Domain\Turn\Contracts\CombatResolver as TurnCombatResolverContract;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(CombatDiceRollerContract::class, RandomDiceRoller::class);
        $this->app->bind(CombatResolverContract::class, CombatResolver::class);
        $this->app->singleton(CombatStateArrayMapper::class);
        $this->app->bind(TurnCombatResolverContract::class, CombatResolverAdapter::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
