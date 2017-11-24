<?php

    namespace Lachezargrigorov\Cart;

    use Illuminate\Support\Collection;
    use Lachezargrigorov\Cart\Exceptions\CartException;
    use Lachezargrigorov\Cart\Helpers\Helpers;
    use Lachezargrigorov\Cart\Traits\CartTrait;

    class Cart
    {
        use CartTrait;

        /** Singleton instance
         * @var
         */
        protected static $instance;


        /** Session key that contains the cart items
         *
         * @var string
         */

        protected $sessionKeyCartItems;

        /** Session key that contains the cart conditions
         *
         * @var string
         */

        protected $sessionKeyCartConditions;

        /**
         * @var \Session
         */

        protected $session;

        /** \Events
         * @var \Event
         */

        protected $events;

        /** Instance name
         * @var string
         */

        protected $instanceName;

        /** Collection of the item models in the cart
         *
         * @var Collection
         */

        protected $models;


        /**
         * @var Lachezargrigorov\Cart\Closures
         */

        protected $closures;


        public function __construct()
        {
            //construct
            $this->sessionKeyCartItems      = $this->config("session_key")."_cart_items";
            $this->sessionKeyCartConditions = $this->config("session_key")."_cart_conditions";
            $this->instanceName             = $this->config("instance_name");
            $this->session                  = app("session");
            $this->events                   = app("events");

            //init
            $this->closures     = Closures::getInstance();
            $this->models       = null;

            if(!$this->session->has($this->sessionKeyCartItems))
            {
                $this->session->put($this->sessionKeyCartItems,new Collection);
            }

            if(!$this->session->has($this->sessionKeyCartConditions))
            {
                $this->session->put($this->sessionKeyCartConditions,new Collection);
            }

            //TODO: not implemented yet
            /*if($this->config("sync_items") instanceof \Closure)
            {
                $this->syncItems();
            }*/
        }

        protected function syncItems()
        {

            $this->items()->each(function($item)
            {
                $model = $this->model($item->id);

                $this->closures->syncItems($model);
            });
        }

        /** Return a singleto instance
         * @return mixed
         */

        public static function getInstance()
        {
            if(is_null(static::$instance))
            {
                static::$instance = new static;
            }

            return static::$instance;
        }

        /** Cart instance name
         * @return string
         */

        public function getInstanceName()
        {
            return $this->instanceName;
        }

        /** Item models to use without quering the Database
         *  Primarily for testing with mock items
         *
         * @param Collection $models
         * @return $this
         */

        public function useModels(Collection $models)
        {
            $this->models = collect($models->getDictionary());
            return $this;
        }

        /**
         * Empty the item models.
         * This will case the cart to reload the models on next model call.
         */

        public function emptyModels()
        {
            $this->models = null;
            return $this;
        }

        /** Get item model by id
         *
         * @param $itemId - id of the item
         *
         * @return mixed
         * @throws CartException
         */

        public function model(int $itemId)
        {
            $this->loadModels();
            if(!$this->models->has($itemId))
            {
                throw new CartException("Something is wrong, model id[{$itemId}] not found in models collection!");
            }

            return $this->models->get($itemId);
        }

        protected function loadModels()
        {

            if($this->models)
            {
                return;
            }

            $mItems = $this->getModelsFromDB();
            if(count($mItems) > 0)
            {
                $this->models = collect($mItems->getDictionary());
            }
            else
            {
                $this->models = collect();
            }

        }

        protected function getModelsFromDB()
        {
            $keys = $this->keys();

            if(count($keys) == 0)
            {
                return [];
            }

            return $this->itemClass()::whereIn("id", $keys)->get();
        }

        /** Get all items in cart
         *
         * @return Collection
         */

        public function items()
        {

            return $this->session->get($this->sessionKeyCartItems);
        }

        /** Get all cart conditions
         *
         * @return Collection
         */

        public function conditions()
        {
            return $this->session->get($this->sessionKeyCartConditions);
        }

        /** Get item from cart by id
         *
         * @param $itemId
         *
         * @return Item
         */

        public function item(int $itemId)
        {
            $item = $this->items()->get($itemId);

            //create
            if(is_null($item))
            {
                $item = new Item($itemId,0);
                $this->items()->put($itemId, $item);

                //reload models on next model call
                $this->emptyModels();
            }

            return $item;
        }

        /** Get or create a cart condition
         *
         * @param string $name
         *
         * @return CartCondition
         */

        public function condition(string $name)
        {
            $condition = $this->conditions()->get($name);

            if(is_null($condition))
            {
                $condition = new CartCondition($name, Condition::TYPE_UNDEFINED, Condition::VALUE_UNDEFINED);
                $this->conditions()->put($name, $condition);

            }

            return $condition;
        }

        /**
         * check if an item exists by item ID
         *
         * @param $itemId
         *
         * @return bool
         */

        public function has(int $itemId)
        {
            return $this->items()->has($itemId);
        }

        /** Check if cart condition exist by condition name
         * @param string $conditionName
         *
         * @return bool
         */

        public function hasCondition(string $conditionName)
        {
            return $this->conditions()->has($conditionName);
        }


        /** Return the count of the items in the cart
         *
         * @return int
         */

        public function count()
        {
            return $this->items()->count();
        }

        /** Return the count of the cart conditions
         * @return int
         */

        public function countConditions()
        {
            return $this->conditions()->count();
        }

        /** Remove items from cart
         * @param array $ids
         *
         * @return $this
         */

        public function remove(array $ids)
        {

            foreach($ids as $id)
            {
                $this->item($id)->remove();
            }

            return $this;
        }

        /** Remove cart conditions
         * @param array $names
         *
         * @return $this
         */

        public function removeConditions(array $names)
        {
            foreach($names as $conditionName)
            {
                $this->condition($conditionName)->remove();
            }

            return $this;
        }

        /** Empty the cart items
         * @return $this
         */

        public function empty()
        {
            $this->models = null;
            $this->session->put($this->sessionKeyCartItems, new Collection);

            return $this;
        }

        /** Empty the cart conditions
         * @return $this
         */

        public function emptyConditions()
        {
            $this->session->put($this->sessionKeyCartConditions,new Collection);

            return $this;

        }

        /** Check if cart is empty
         *
         * @return bool
         */

        public function isEmpty()
        {
            return $this->items()->isEmpty();
        }

        /** Check if cart conditions are empty
         * @return bool
         */

        public function isEmptyConditions()
        {
            return $this->conditions()->isEmpty();
        }

        /** Return the keys of the items in the cart
         *
         * @return Collection
         */

        public function keys()
        {
            return $this->items()->keys();
        }

        /** Return the names of the cart conditions
         * @return Collection
         *
         */
        public function keysOfConditions()
        {
            return $this->conditions()->keys();
        }

        /**
         * Get total quantity of the items in the cart
         *
         * @return int
         */

        public function totalQuantity()
        {
            if($this->isEmpty())
            {
                return 0;
            }
            $count = $this->items()->sum(function($item)
            {
                return $item->quantity;
            });

            return $count;
        }

        /** Get cart subtotal without applied item conditions
         * @return mixed
         */

        public function subtotalWithoutConditions()
        {
            return $this->items()->sum(function($item)
            {
                return $item->priceSumWithoutConditions();
            });
        }

        /** Get cart subtotal with applied item conditions
         * @return mixed
         */

        public function subtotal()
        {
            return $this->items()->sum(function($item)
            {
                return $item->priceSum();
            });
        }

        /** Get cart total with applied cart conditions
         * @return float
         */
        public function total()
        {
            $subtotal = $this->subtotal();
            foreach($this->conditions() as $condition)
            {
                $subtotal = $condition->apply($subtotal);
            }

            return Helpers::numberFormat($subtotal);
        }

        /** Get item class
         * @return class
         */

        protected function itemClass()
        {
            return $this->config("item_class");
        }

        //TODO: Events are not implemented yet
        /**
         * @param $name
         * @param $value
         * @return mixed
         */

        protected function fireEvent($name, $value = [])
        {
            return $this->events->fire($this->getInstanceName() . '.' . $name, array_values([$value, $this]));
        }
    }
