<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Lachezargrigorov\Cart\Iterfaces\Item;

class Product extends Model implements Item
{
    //

    public function getCartPrice()
    {
        return $this->price;
    }


}
