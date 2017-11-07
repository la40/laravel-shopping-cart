<?php namespace Lachezargrigorov\Cart\Traits;

use Lachezargrigorov\Cart\Condition;
use Lachezargrigorov\Cart\Helpers\Helpers;

trait CartTrait
{
    /** Get cart config
     * @param null $key
     *
     * @return mixed
     */
    public function config($key = null)
    {
        $config = app()->environment("testing") ? require(__DIR__.'/../../tests/Mock/config.php') : config("cart");
        return array_get($config,$key);
    }

    /** Get cart instance
     * @return Cart
     */
    protected function cart()
    {
        return app($this->config("instance_name"));
    }


    /** Set condition or conditions as array
     * @param array $condition - as array [condition] or multidimensional array with conditions [[condition],[condition]] (already existing conditions will be merged with currents)
     *
     * @return $this
     */

    public function setConditionAsArray(array $condition)
    {
        //ignore if class has not method "condition"
        if(!method_exists($this,"condition")) return $this;

        if(Helpers::isMultiArray($condition))
        {
            foreach($condition as $aCondition)
            {
                $this->setConditionAsArray($aCondition);
            }
        }
        else
        {
            $this->condition($condition[ "name" ])->set($condition);
        }
        return $this;
    }
}
