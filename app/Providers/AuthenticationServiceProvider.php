<?php

namespace App\Providers;

use App\Interfaces\Authentication\{
    LoginInterface,
    MeInterface,
    RegisterInterface,
};

use App\Repositories\Authentication\{
    LoginRepository,
    MeRepository,
    RegisterRepository,
};

use Illuminate\Support\ServiceProvider;

class AuthenticationServiceProvider extends ServiceProvider {
    public function register(): void
    {
        // Bind LoginInterface to LoginRepository (single instance)
        $this->app->singleton(LoginInterface::class, LoginRepository::class);
        $this->app->singleton(RegisterInterface::class, RegisterRepository::class);

        // Bind MeInterface to MeRepository (single instance)
        $this->app->singleton(MeInterface::class, MeRepository::class);
    }
}