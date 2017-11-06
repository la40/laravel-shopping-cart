<?php namespace Lachezargrigorov\Cart\Collections;

use Illuminate\Support\Collection as LaravelCollection;

class Collection extends LaravelCollection
{

    public function __get($key)
    {
        if( $this->has($key) ) return $this->get($key);
        return parent::__get($key);
    }
}
