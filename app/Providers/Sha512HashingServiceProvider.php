<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Libraries\Hashers\Sha512HasherLibrary;

class Sha512HashingServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $hash = $this->app['hash'];

        $hash->extend('sha512', function(){
            return new Sha512HasherLibrary();
        });
    }
}