<?php

namespace App\Providers;

use Illuminate\Console\OutputStyle;
use Illuminate\Support\Collection;
use Illuminate\Support\ServiceProvider;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\StreamOutput;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        date_default_timezone_set(config('app.timezone'));

        Collection::macro('recursive', function () {
            return $this->map(function ($value) {
                if (is_array($value) || is_object($value)) {
                    return collect($value)->recursive();
                }

                return $value;
            });
        });

        $this->app->bind('console.output', function () {
            return new OutputStyle(
                new StringInput(''),
                new StreamOutput(fopen('php://stdout', 'w'))
            );
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
