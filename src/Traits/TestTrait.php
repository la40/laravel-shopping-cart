<?php namespace Lachezargrigorov\Cart\Traits;

use Lachezargrigorov\Cart\Cart;
use Faker\Generator as FakerGenerator;
use Illuminate\Database\Eloquent\Factory as EloquentFactory;

trait TestTrait
{
    protected $cart;
    protected $config;
    protected $itemFactory;

    protected $saleCondition = ["name" => "sale", "type" => "sale", "value" => "-10%","attributes" => ["size" => "M"]];
    protected $bonusCondition = ["name" => "bonus", "type" => "bonus", "value" => "+2","attributes" => ["size" => "S"]];

    public function setUp()
    {
        parent::setUp();

        $this->cart = new Cart();

        $this->app->singleton($this->cart->config("instance_name"), function($app)
        {
            return $this->cart;
        });

    }

    /* public function tearDown()
    {
        parent::tearDown();
    }*/

    protected function initFactory()
    {
        return EloquentFactory::construct($this->app->make(FakerGenerator::class), __DIR__."/../../tests/Factories");;
    }

    protected function mockItems($times = 2)
    {
        return $this->initFactory()->of($this->cart->config("item_class"))->times($times)->make();
    }

}
