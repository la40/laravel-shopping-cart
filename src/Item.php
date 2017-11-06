<?php namespace Lachezargrigorov\Cart;

use Illuminate\Support\Collection;
use Lachezargrigorov\Cart\Collections\ItemAttributesCollection;
use Lachezargrigorov\Cart\Exceptions\ItemException;
use Lachezargrigorov\Cart\Exceptions\ConditionException;
use Lachezargrigorov\Cart\Helpers\Helpers;
use Lachezargrigorov\Cart\Traits\CartTrait;

class Item
{
    use CartTrait;

    /** Isolated item properties container
     * @var \stdClass
     */

    protected $item;

    /**
     * Item constructor.
     *
     * @param int                      $id
     * @param int                      $quantity
     * @param ItemAttributesCollection $attributes
     * @param Collection $conditions
     */
    public function __construct(int $id, int $quantity, array $attributes = null, array $conditions = null)
    {
        $this->item = new \stdClass;

        $this->item->id = $id;
        $this->item->quantity = $quantity;

        $this->item->attributes = new ItemAttributesCollection;
        if(!is_null($attributes)) $this->attributes($attributes);

        $this->item->conditions = new Collection;
        if(!is_null($conditions)) $this->setConditionAsArray($conditions);
    }

    /** Get property by key from isolated properties container
     *
     * @param $key
     *
     * @return mixed|null
     */

    public function __get($key)
    {
        if($key === "model")
        {
            return $this->cart()->model($this->item->id);
        }

        return $this->item->{$key};
    }

    /** Set quantity
     * @param $quantity
     *
     * @return $this
     */

    public function quantity(int $quantity)
    {
        $this->item->quantity = $quantity;

        return $this;
    }

    /** Add quantity
     * @param $quantity
     *
     * @return $this
     */

    public function addQuantity(int $quantity)
    {
        $this->item->quantity += $quantity;

        return $this;

    }

    /** Set attributes
     * @param array|null $attributes - array with attributes (attributes will be merged with current) or null to empty them
     *
     * @return $this
     */

    public function attributes(array $attributes)
    {
        $this->item->attributes = $this->item->attributes->merge($attributes);

        return $this;
    }

    /** Empty attributes
     * @return $this
     */

    public function emptyAttributes()
    {
        $this->item->attributes =  new ItemAttributesCollection;

        return $this;
    }

    /** Get all item conditions
     * @return Collection
     */
    public function conditions()
    {
        return $this->item->conditions;
    }

    /** Get or create an item condition
     * @param string $name
     */

    public function condition(string $name)
    {
        $condition = $this->conditions()->get($name);

        if(is_null($condition))
        {
            $condition = new ItemCondition($name,Condition::TYPE_UNDEFINED, Condition::VALUE_UNDEFINED, $this->item->id);
            $this->conditions()->put($name,$condition);
        }

        return $condition;
    }


    /** Check if conditions are empty
     * @return bool
     */

    public function isEmptyConditions()
    {
        return $this->item->conditions->isEmpty();
    }

    /** Check item for condition
     * @param $name
     *
     * @return bool
     */

    public function hasCondition($name)
    {
        return $this->item->conditions->get($name) != null;
    }


    /**  Remove all condtion
     * @return $this
     */

    public function emptyConditions()
    {
        $this->item->conditions = new Collection;

        return $this;
    }

    /** Set quantity, attributes and conditions as array
     * @param $arr - keys: quantity - change quantity, add_quantity - add value to current quantity, attributes, conditions
     *
     * @return $this
     */

    public function set($arr)
    {
        if(array_key_exists("quantity",$arr)) $this->quantity($arr["quantity"]);
        if(array_key_exists("add_quantity",$arr)) $this->addQuantity($arr["add_quantity"]);
        if(array_key_exists("attributes",$arr)) $this->attributes($arr["attributes"]);
        if(array_key_exists("conditions",$arr)) $this->setConditionAsArray($arr["conditions"]);

        return $this;
    }

    /** Remove current item from cart
     * @return $this
     */

    public function remove()
    {
        $this->cart()->items()->forget($this->item->id);
        return $this;
    }

    /** Get price without conditions
     * @return double
     */

    public function priceWithoutConditions()
    {
        return Helpers::numberFormat($this->model->getCartPrice());
    }

    /** Get price without conditions for current quantity
     * @return double
     */

    public function priceSumWithoutConditions()
    {
        return Helpers::numberFormat($this->item->quantity * $this->priceWithoutConditions());
    }

    /** Get price with conditions
     * @return double
     */

    public function price()
    {
        $price = $this->priceWithoutConditions();
        foreach($this->item->conditions as $condition)
        {
            $price = $condition->apply($price);
        }

        return Helpers::numberFormat($price);
    }

    /** Get price with conditions for current quantity
     * @return double
     */

    public function priceSum()
    {
        return Helpers::numberFormat($this->item->quantity * $this->price());
    }
}