<?php

namespace App\Providers;

use App\Interfaces\Utilities\UserInterface;
use App\Repositories\Utilities\UserRepository;

use Illuminate\Support\ServiceProvider;

class UtilitiesServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(UserInterface::class, UserRepository::class);
    }
}   


