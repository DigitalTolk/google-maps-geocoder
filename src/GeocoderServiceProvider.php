<?php

namespace Jestillore\GoogleMapsGeocoder;

use Illuminate\Support\ServiceProvider;

class GeocoderServiceProvider extends ServiceProvider
{
    /**
     * Register the config
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/geocoder.php', 'geocoder');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/geocoder.php' => config_path('geocoder.php')
            ]);
        }

        $this->app->singleton(Geocoder::class, function () {
            $geocoder = new Geocoder();
            $geocoder->setApiKey($this->app->make('config')->get('geocoder.key'));
            return $geocoder;
        });
    }

    /**
     * @return array
     */
    public function provides()
    {
        return [
            Geocoder::class
        ];
    }
}
