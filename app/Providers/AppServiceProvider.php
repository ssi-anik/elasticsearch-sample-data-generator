<?php

namespace App\Providers;

use Faker\Factory;
use Faker\Generator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    protected $fakerProviders = [
        FakerProvider::class,
    ];

    public function boot()
    {
        $this->app->singleton(Generator::class, function ($app) {
            $faker = Factory::create();
            foreach ($this->fakerProviders as $provider) {
                $faker->addProvider(new $provider($faker));
            }

            return $faker;
        });
    }
}
