<?php namespace Lachezargrigorov\Cart;

class CartCondition extends Condition
{
    /** Remove current cart condition
     * @return $this
     */

    public function remove()
    {
        $this->cart()->conditions()->forget($this->condition->name);
        return $this;
    }

    /**
     * @inheritdoc
     */

    protected function syncName()
    {
        $this->cart()->conditions()->put($this->condition->name, $this)->forget($this->lastName);
    }

}