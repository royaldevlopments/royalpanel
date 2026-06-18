<?php

namespace RoyalPanel\Providers;

use Illuminate\Support\ServiceProvider;
use RoyalPanel\Repositories\Eloquent\EggRepository;
use RoyalPanel\Repositories\Eloquent\NestRepository;
use RoyalPanel\Repositories\Eloquent\NodeRepository;
use RoyalPanel\Repositories\Eloquent\TaskRepository;
use RoyalPanel\Repositories\Eloquent\UserRepository;
use RoyalPanel\Repositories\Eloquent\ApiKeyRepository;
use RoyalPanel\Repositories\Eloquent\ServerRepository;
use RoyalPanel\Repositories\Eloquent\SessionRepository;
use RoyalPanel\Repositories\Eloquent\SubuserRepository;
use RoyalPanel\Repositories\Eloquent\DatabaseRepository;
use RoyalPanel\Repositories\Eloquent\LocationRepository;
use RoyalPanel\Repositories\Eloquent\ScheduleRepository;
use RoyalPanel\Repositories\Eloquent\SettingsRepository;
use RoyalPanel\Repositories\Eloquent\AllocationRepository;
use RoyalPanel\Contracts\Repository\EggRepositoryInterface;
use RoyalPanel\Repositories\Eloquent\EggVariableRepository;
use RoyalPanel\Contracts\Repository\NestRepositoryInterface;
use RoyalPanel\Contracts\Repository\NodeRepositoryInterface;
use RoyalPanel\Contracts\Repository\TaskRepositoryInterface;
use RoyalPanel\Contracts\Repository\UserRepositoryInterface;
use RoyalPanel\Repositories\Eloquent\DatabaseHostRepository;
use RoyalPanel\Contracts\Repository\ApiKeyRepositoryInterface;
use RoyalPanel\Contracts\Repository\ServerRepositoryInterface;
use RoyalPanel\Repositories\Eloquent\ServerVariableRepository;
use RoyalPanel\Contracts\Repository\SessionRepositoryInterface;
use RoyalPanel\Contracts\Repository\SubuserRepositoryInterface;
use RoyalPanel\Contracts\Repository\DatabaseRepositoryInterface;
use RoyalPanel\Contracts\Repository\LocationRepositoryInterface;
use RoyalPanel\Contracts\Repository\ScheduleRepositoryInterface;
use RoyalPanel\Contracts\Repository\SettingsRepositoryInterface;
use RoyalPanel\Contracts\Repository\AllocationRepositoryInterface;
use RoyalPanel\Contracts\Repository\EggVariableRepositoryInterface;
use RoyalPanel\Contracts\Repository\DatabaseHostRepositoryInterface;
use RoyalPanel\Contracts\Repository\ServerVariableRepositoryInterface;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register all the repository bindings.
     */
    public function register(): void
    {
        // Eloquent Repositories
        $this->app->bind(AllocationRepositoryInterface::class, AllocationRepository::class);
        $this->app->bind(ApiKeyRepositoryInterface::class, ApiKeyRepository::class);
        $this->app->bind(DatabaseRepositoryInterface::class, DatabaseRepository::class);
        $this->app->bind(DatabaseHostRepositoryInterface::class, DatabaseHostRepository::class);
        $this->app->bind(EggRepositoryInterface::class, EggRepository::class);
        $this->app->bind(EggVariableRepositoryInterface::class, EggVariableRepository::class);
        $this->app->bind(LocationRepositoryInterface::class, LocationRepository::class);
        $this->app->bind(NestRepositoryInterface::class, NestRepository::class);
        $this->app->bind(NodeRepositoryInterface::class, NodeRepository::class);
        $this->app->bind(ScheduleRepositoryInterface::class, ScheduleRepository::class);
        $this->app->bind(ServerRepositoryInterface::class, ServerRepository::class);
        $this->app->bind(ServerVariableRepositoryInterface::class, ServerVariableRepository::class);
        $this->app->bind(SessionRepositoryInterface::class, SessionRepository::class);
        $this->app->bind(SettingsRepositoryInterface::class, SettingsRepository::class);
        $this->app->bind(SubuserRepositoryInterface::class, SubuserRepository::class);
        $this->app->bind(TaskRepositoryInterface::class, TaskRepository::class);
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
    }
}
