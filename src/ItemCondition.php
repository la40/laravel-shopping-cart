<?php namespace Lachezargrigorov\Cart;

class ItemCondition extends Condition
{
    /** The ID of the item that contain this ItemCondition
     *
     * @var
     */
    private $itemId;

    /**
     * ItemCondition constructor.
     *
     * @param string     $name
     * @param string     $type
     * @param string     $value
     * @param int        $itemId
     * @param array|null $attributes
     */

    public function __construct(string $name, string $type, string $value, int $itemId, array $attributes = null)
    {
        parent::__construct($name, $type, $value, $attributes);
        $this->itemId = $itemId;
    }

    /** Remove current item condition
     *
     * @return $this
     */

    public function remove()
    {
        $this->cart()->item($this->itemId)->conditions()->forget($this->condition->name);
        return $this;
    }

    /**
     * @inheritdoc
     */

    protected function syncName()
    {
        $this->cart()->item($this->itemId)->conditions()->put($this->condition->name, $this)->forget($this->lastName);
    }
}