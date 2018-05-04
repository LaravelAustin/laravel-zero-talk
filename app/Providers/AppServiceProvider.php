<?php

namespace App\Providers;

use GuzzleHttp\Client;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(Client::class, function () {
            return new Client([
                'base_uri' => config('meetup.url'),
                'query' => [
                    'key' => config('meetup.api_key'),
                    'sign' => true,
                ],
            ]);
        });
    }
}
