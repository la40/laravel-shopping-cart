<?php namespace Lachezargrigorov\Cart;

use Lachezargrigorov\Cart\Collections\ConditionAttributesCollection;
use Lachezargrigorov\Cart\Exceptions\ConditionException;
use Lachezargrigorov\Cart\Helpers\Helpers;
use Lachezargrigorov\Cart\Traits\CartTrait;

abstract class Condition
{
    use CartTrait;

    /** Sync the name of the condition with the key that holds it
     * @return mixed
     */
    abstract protected function syncName();

    const TYPE_UNDEFINED = "undefined";
    const VALUE_UNDEFINED = "undefined";

    /** Isolated properties container
     * @var \stdClass
     */

    protected $condition;

    /** Last known condition name
     * @var
     */
    protected $lastName;

    /**
     * Condition constructor.
     *
     * @param string     $name
     * @param string     $type
     * @param string     $value
     * @param array|null $attributes
     */

    public function __construct(string $name, string $type, string $value, array $attributes = null)
    {

        $this->condition = new \stdClass;

        $this->condition->name = $this->lastName = $name;
        $this->condition->type = $type;
        $this->condition->value = $value;

        $this->condition->attributes = new ConditionAttributesCollection();
        if(!is_null($attributes)) $this->setAttributes($attributes);
    }

    /** Get property by key from isolated properties container
     *
     * @param $key
     *
     * @return mixed|null
     */

    public function __get($key)
    {
        return $this->condition->{$key};
    }

    /** Set condition properties as array
     *  Accepted properties: name, type, value, attributes
     *
     * @param array $condition
     *
     * @return $this
     */

    public function set(array $condition)
    {
        if(array_key_exists("name",$condition)) $this->name($condition["name"]);
        if(array_key_exists("type",$condition)) $this->type($condition["type"]);
        if(array_key_exists("value",$condition)) $this->value($condition["value"]);
        if(array_key_exists("attributes",$condition)) $this->attributes($condition["attributes"]);

        return $this;
    }

    /** Set name
     *  This will change on save the key containing this condition in cart conditions collection!
     * @param string $name
     *
     * @return $this
     */

    public function name(string $name)
    {
        //ignore if the same name set
        if($this->condition->name == $name) return $this;

        $this->condition->name = $name;

        $this->syncName();

        $this->lastName = $name;

        return $this;
    }



    /** Set type
     * @param string $type
     *
     * @return $this
     */

    public function type(string $type)
    {
        $this->condition->type = $type;

        return $this;
    }

    /** Set value
     * @param string $value
     *
     * @return $this
     */

    public function value(string $value)
    {
        $this->condition->value = $value;

        return $this;
    }

    /** Set attributes
     * @param array|null $attributes - array with attributes (attributes will be merged with current) or null to empty them
     *
     * @return $this
     */

    public function attributes(array $attributes)
    {
        $this->condition->attributes = $this->condition->attributes->merge($attributes);

        return $this;
    }

    /** Empty attributes
     * @return $this
     */
    public function emptyAttributes()
    {
        $this->condition->attributes =  new ConditionAttributesCollection;

        return $this;
    }

    /** Apply condition
     * @param $price - current price
     *
     * @return int
     */

    public function apply($price)
    {
        //ignore conditions with undefined values
        if($this->condition->value === static::VALUE_UNDEFINED) return $price;

        $result = 0;
        $cleanValue = Helpers::normalizePrice($this->cleanValue());

        if($this->valueContainsPercentage())
        {
            $result = ($this->valueContainsSubstract()) ? $price - ($price * ($cleanValue / 100)) : $price + ($price * ($cleanValue / 100));
        }
        else
        {
            $result = ($this->valueContainsSubstract()) ? $price - $cleanValue : $price + $cleanValue;
        }

        return $result < 0 ? 0 : $result;
    }

    /**
     * Check if value contains a percentage
     *
     * @return bool
     */

    protected function valueContainsPercentage()
    {
        return (preg_match('/%/', $this->condition->value) == 1);
    }

    /**
     * Check if value contains a subtract symbol
     *
     * @return bool
     */

    protected function valueContainsSubstract()
    {
        return (preg_match('/\-/', $this->condition->value) == 1);
    }

    /**
     * Check if value contains a add symbol
     *
     * @param $value
     *
     * @return bool
     */

    protected function valueContainsAdd()
    {
        return (preg_match('/\+/', $this->condition->value) == 1);
    }

    /**
     * Clean given value by removing %,+,- symbols only
     *
     * @param $value
     *
     * @return mixed
     */

    protected function cleanValue()
    {
        return str_replace([
            '%',
            '-',
            '+',
        ], '', $this->condition->value);
    }

    /** Validate condition
     * @param array $condition
     *
     * @throws ConditionException
     */

    public static function validate(array $condition)
    {
        $rules = [
            'name'   => 'required',
            'type'   => 'required',
            'value'  => 'required',
        ];

        $validator = \Validator::make($condition, $rules);
        if($validator->fails())
        {
            throw new ConditionException($validator->messages()->first());
        }
    }
}