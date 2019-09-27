<?php

namespace App\Providers;

use App\Extensions\Inventory;
use App\Translations;
use Illuminate\Support\ServiceProvider;
use Lokalise\LokaliseApiClient;
use Symfony\Component\Yaml\Yaml;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(Inventory::class);

        $this->app->singleton(LokaliseApiClient::class, function () {
            return new LokaliseApiClient(env('LOKALISE_API_TOKEN'));
        });

        $this->app->singleton(Translations::class, function () {
            return new Translations(Yaml::parseFile(base_path('translations.yml')));
        });
    }
}
