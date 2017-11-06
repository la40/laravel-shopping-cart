<?php namespace Lachezargrigorov\Cart\Iterfaces;

interface Item
{

    //TODO: This is not implemented yet
    /** Checks whether the item is active
     * @return bool
     */

    //public function isActive();

    //TODO: This is not implemented yet
    /** Checks weather the item is orderable
     * @return bool
     */

    //public function isOrderable();

    /** Return the price
     * @return mixed
     */
    public function getCartPrice();
}
