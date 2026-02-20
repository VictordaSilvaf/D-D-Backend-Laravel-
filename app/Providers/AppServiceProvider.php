<?php

namespace App\Providers;

use App\Domain\Combat\CombatResolver;
use App\Domain\Combat\Contracts\CombatResolver as CombatResolverContract;
use App\Domain\Combat\Contracts\DiceRoller as CombatDiceRollerContract;
use App\Domain\Combat\RandomDiceRoller;
use App\Domain\Turn\CombatResolverAdapter;
use App\Domain\Turn\CombatStateArrayMapper;
use App\Domain\Turn\Contracts\CombatResolver as TurnCombatResolverContract;
use App\Domain\Turn\Contracts\DiceRoller as TurnDiceRollerContract;
use App\Domain\Turn\Contracts\NarrationService as NarrationServiceContract;
use App\Domain\Turn\Contracts\NpcDecisionService as NpcDecisionServiceContract;
use App\Domain\Turn\DiceRoller as TurnDiceRoller;
use App\Domain\Turn\Services\NarrationService;
use App\Domain\Turn\Services\NpcDecisionService;
use App\Models\GameSession;
use App\Policies\GameSessionPolicy;
use Illuminate\Support\Facades\Gate;
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
        $this->app->bind(TurnDiceRollerContract::class, TurnDiceRoller::class);
        $this->app->bind(NpcDecisionServiceContract::class, NpcDecisionService::class);
        $this->app->bind(NarrationServiceContract::class, NarrationService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(GameSession::class, GameSessionPolicy::class);
    }
}
