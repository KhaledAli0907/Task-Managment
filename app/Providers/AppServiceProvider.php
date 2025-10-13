<?php

namespace App\Providers;

use App\Services\Implementations\AuthService;
use App\Services\Implementations\TaskService;
use App\Services\Interfaces\AuthServiceInterface;
use App\Services\Interfaces\TaskServiceInterface;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(AuthServiceInterface::class, AuthService::class);
        $this->app->bind(TaskServiceInterface::class, TaskService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
