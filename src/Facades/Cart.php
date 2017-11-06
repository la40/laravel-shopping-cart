<?php namespace Lachezargrigorov\Cart\Facades;

use Illuminate\Support\Facades\Facade;

class Cart extends Facade {

    protected static function getFacadeAccessor()
    {
        return config("cart.instance_name");
    }
}