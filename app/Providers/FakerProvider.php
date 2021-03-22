<?php

namespace App\Providers;

use Faker\Provider\Base;

use Illuminate\Support\Arr;

class FakerProvider extends Base
{
    public function custom($path)
    {
        return static::randomElement(Arr::wrap(config("source.{$path}")));
    }
}
