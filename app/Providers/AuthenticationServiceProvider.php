<?php

namespace App\Providers;

use App\Interfaces\Authentication\{
    LoginInterface,
    MeInterface,
};

use App\Repositories\Authentication\{
    LoginRepository,
    MeRepository,
};

use Illuminate\Support\ServiceProvider;

class AuthenticationServiceProvider extends ServiceProvider {
    public function register(): void
    {
        $this->app->singleton(LoginInterface::class, LoginRepository::class);
        $this->app->singleton(MeInterface::class, MeRepository::class);
    }
}