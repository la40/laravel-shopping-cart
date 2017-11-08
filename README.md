# Laravel Shopping Cart

[![Latest Stable Version](https://poser.pugx.org/lachezargrigorov/laravel-shopping-cart/v/stable)](https://packagist.org/packages/lachezargrigorov/laravel-shopping-cart)
[![Latest Unstable Version](https://poser.pugx.org/lachezargrigorov/laravel-shopping-cart/v/unstable)](https://packagist.org/packages/lachezargrigorov/laravel-shopping-cart)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg)](LICENSE.md)
[![Build Status](https://img.shields.io/travis/lachezargrigorov/laravel-shopping-cart/master.svg)](https://travis-ci.org/lachezargrigorov/laravel-shopping-cart)
[![Total Downloads](https://poser.pugx.org/lachezargrigorov/laravel-shopping-cart/downloads)](https://packagist.org/packages/lachezargrigorov/laravel-shopping-cart)

An easy to use more advanced shopping cart for Laravel applications.

## Installation

### Via Composer

``` bash
$ composer require lachezargrigorov/laravel-shopping-cart
```

Laravel 5.5 and above uses Package Auto-Discovery, so doesn't require you to manually add the ServiceProvider and Facade to the array in config/app.php

If you don't use auto-discovery, add the CartServiceProvider to the providers array in config/app.php

```php
\Lachezargrigorov\Cart\CartServiceProvider::class,
```

If you want to use the Cart facade, add this to the aliases array in app.php:

```php
'Cart' => \Lachezargrigorov\Cart\Facades\Cart::class,
```

Implement the Item interface in your product model. 
The Cart and Item uses ***getCartPrice*** method to calculate the totals.

```php
use Illuminate\Database\Eloquent\Model;
use Lachezargrigorov\Cart\Iterfaces\Item;

class Product extends Model implements Item {}
```

Publish the package config to your local config with the publish command and configure them:

```php
php artisan vendor:publish --provider="Lachezargrigorov\Cart\CartServiceProvider" --tag="config"
```

Set the item_class in local package config (cart.php) 

``` php
"item_class" => \App\Product::class,
```

## Legend

#### Elements 

- Cart : class Lachezargrigorov\Cart\Cart
- Item : class Lachezargrigorov\Cart\Item
- Condition : abstract class Lachezargrigorov\Cart\Condition;
- ItemCondition : class Lachezargrigorov\Cart\ItemCondition extends Condition
- CartCondition : class Lachezargrigorov\Cart\CartCondition extends Condition

#### Interfaces

- Item : interface Lachezargrigorov\Cart\Interfaces\Item

#### Collections

- LaravelCollection : class Illuminate\Support\Collection
- Collection : class Lachezargrigorov\Cart\Collections\Collection extends LaravelCollection
- ItemAttributesCollection : class Lachezargrigorov\Cart\Collections\ItemAttributesCollection extends Collection
- ConditionAttributesCollection : class Lachezargrigorov\Cart\Collections\ConditionAttributesCollection extends Collection

#### Exceptions

- CartException : class Lachezargrigorov\Cart\Exceptions\CartException


## Usage`

### Cart

#### Item

##### Add or get Item

This method add or get an Item if exist.

- $id : int - the id of the item (product) model
- return : Item

``` php
Cart::item($id);  //quantity = 0 on create
```

##### Models loading process
For better performance models are lazy associated to the items on first '$item->model' call after init or item addition in single DB request so you don't need to add any extra data like name, price, etc. 

``` php
Cart::item(1);
Cart::item(2)->addQuantity(1);

//models are not loaded yet

//models are lazy loaded here
Cart::item(1)->model;

//if item not exist already, add a new one and mark that models need to be loaded again on next "$item->model" call
Cart::item(3);
Cart::item(4);

//models are not loaded again

//models are lazy loaded here again
Cart::item(4)->model;
```

##### Remove Item
-return : removed Item

``` php
Cart::item($id)->remove(); 
```

##### Get items
- return : LaravelCollection with Items

``` php
Cart::items(); 
```

##### Has item (Item)
- id : int - the id of the item (product) model
- return : bool

``` php
Cart::has($id); 
```

##### Get items count
- return : int

``` php
Cart::count(); 
```

##### Remove items (Item)
- ids : [] - array with ids 
- return : Cart

``` php
Cart::remove($ids);
```

##### Remove all cart items
- return : Cart
 
``` php
Cart::empty();
```

##### Is empty for items
- return : bool

``` php
Cart::isEmpty();
```

##### Get item keys
- return : LaravelCollection with keys 

``` php
Cart::keys();
```

#### CartCondition

##### Add or get CartCondition
This method add or get an CartCondition if exist.

- name : condition name
- return : CartCondition

``` php
Cart::condition($name);
```

#### Add or set CartConditions as array
This will rewrite the existing conditions.

- return : Cart

``` php

    Cart::setConditionAsArray([
               "name"  => "all sale1",
               "type"  => "all sale",
               "value" => "-10%",
           ]);

    //or as multidimensional array

  Cart::setConditionAsArray([
            [
                "name"  => "all sale1",
                "type"  => "all sale",
                "value" => "-10%",
            ],
            [
                "name"  => "all sale2",
                "type"  => "all sale",
                "value" => "+1",
            ],
        ]);
```

##### Get conditions

- return : LaravelCollection with CartConditions

``` php
Cart::conditions(); 
```

##### Has condition
- name : condition name
- return : bool

``` php
Cart::hasCondition($name); 
```

##### Get conditions count
- return : int

``` php
Cart::countConditions(); 
```

##### Remove conditions
- names : array - array with names 
- return : Cart
 
``` php
Cart::removeConditions($names);
```

##### Remove all cart conditions
- return : Cart
 
``` php
Cart::emptyConditions();
```

##### Is empty for conditions
- return : bool

``` php
Cart::isEmptyConditions();
```

##### Get condition keys (names)
- return : LaravelCollection with keys 

``` php
Cart::keysOfConditions();
```

#### Total methods

##### Get total quantity
- return : int 

``` php
Cart::totalQuantity();
```

##### Get cart subtotal without applied ItemConditions and CartConditions
- return : double 

``` php
Cart::subtotalWithoutConditions();
```

##### Get cart subtotal without applied CartConditions
- return : double 

``` php
Cart::subtotal();
```

##### Get cart total with applied ItemConditions and CartConditions
- return : double 

``` php
Cart::total();
```

### Item

#### Properties
- id : int
- quantity : int
- attributes : ItemAttributesCollection
- conditions : LaravelCollection with ItemConditions
- model : Illuminate\Database\Eloquent\Model

##### Set quantity
- return : Item

``` php
Cart::item($id)->quantity(1); 
```

##### Add quantity (current quantity + added quantity)
- return : Item

``` php
Cart::item($id)->addQuantity(1); 
```

##### Get quantity
- return : int

``` php
Cart::item($id)->quantity; 
```

##### Set attributes
- return : Item

``` php
Cart::item($id)->attributes(["size" => "L", "color" => "blue"]); 
```

##### Get attributes
- return : ItemAttributesCollection

``` php
$attributesCollection = Cart::item($id)->attributes;
$itemSize = $attributesCollection->size;
$itemColor = $attributesCollection->color;

 //or

Cart::item($id)->attributes->size;
Cart::item($id)->attributes->color;

//or using LaravelCollection methods

Cart::item($id)->attributes->has("size");
Cart::item($id)->attributes->get("size");
Cart::item($id)->attributes->each(function($value, $key){
    ...
});
```

##### Empty attributes (delete all attributes)
- return : Item

``` php
Cart::item($id)->emptyAttributes(); 
```

##### Add or get ItemCondition
- name : string - condition name
- return : ItemCondition

``` php
Cart::item($id)->condition($name); 
```

#### Add or set ItemConditions as array
This will rewrite the existing conditions.

- return : Item

``` php

    Cart::item(1)->setConditionAsArray([
               "name"  => "item sale1",
               "type"  => "item sale",
               "value" => "-10%",
           ]);

    //or as multidimensional array

  Cart::item(1)->setConditionAsArray([
            [
                "name"  => "item sale1",
                "type"  => "item sale",
                "value" => "-10%",
            ],
            [
                "name"  => "item sale2",
                "type"  => "item sale",
                "value" => "+1",
            ],
        ]);
```

##### Get conditions
- return : LaravelCollection with ItemConditions

``` php
Cart::item($id)->conditions(); 
```

##### Remove ItemCondition
return : removed ItemCondition
``` php
Cart::item($id)->condition($name)->remove(); 
```

##### Has condition
- name : string - condition name
- return : bool
``` php
Cart::item($id)->hasCondition($name); 
```

##### Does item contain any conditions
- return : bool

``` php
Cart::item($id)->isEmptyConditions(); 
```

##### Empty conditions (remove all conditions)
- return : Item

``` php
Cart::item($id)->emptyConditions(); 
```

##### Get model
- return : Illuminate\Database\Eloquent\Model
``` php
Cart::item($id)->model; 
```

#### Price methods

##### Get price without applied conditions
-return : double

``` php
Cart::item($id)->priceWithoutConditions(); 
```

##### Get price sum without applied conditions (price * quantity)
-return : double

``` php
Cart::item($id)->priceSumWithoutConditions(); 
```

##### Get price with applied conditions
-return : double

``` php
Cart::item($id)->price(); 
```

##### Get price sum with applied conditions (price * quantity)
-return : double

``` php
Cart::item($id)->priceSum(); 
```

#### Set helper
- [] : array - quantity : int (not required), add_quantity : int (not required), attributes : array (not required), conditions : array (not required)
- return : Item

``` php
  Cart::item(1)->set([
            "quantity" => 1,
            //"add_quantity" => 2,
            "attributes" => [
                "size" => "S",
            ],
            "conditions" => [
                [
                    "name"  => "whole sale",
                    "type"  => "all sale",
                    "value" => "10%",
                    "attributes" => ["some" => "attribute"]
                ], [
                    "name"  => "item sale",
                    "type"  => "item sale",
                    "value" => "-1",
                ]
            ]

        ]);
              
    // equel to
    
   Cart::item(1)->quantity(1)/*->addQuantity(2)*/->attributes(["size" => "S"])->condition('whole sale')->type("all sale")->value("10%")->attributes(["some" => "attribute"]);
   Cart::item(1)->condition("item sale")->type("item sale")->value("-1");
```

### Condition (CartCondition and ItemCondition)

#### Properties
- name : string - condition name and it's collection key are always the same
- type : string
- value : string - [+-]?[0-9]+(\\.[0-9]+)?[%]? examples: +10%, 10%, 10.22% -10%, +10.11, -10.80 
- attributes : ConditionAttributesCollection

##### Set name
This will change the key in collection too.
- name : string
- return : CartCondition | ItemCondition

``` php
// CartCondition 

//this will create a new condition with name and key = "all sale"
Cart::condition("all sale"); 

//this will change the name and the key in collection too
Cart::condition("all sale")->name("friday sale");

//now this condition is accessible with the new key (name)
Cart::condition("friday sale");

// ItemCondition 

//this will create a new item condition with name and key = "all sale"
Cart::item(1)->condition("all sale"); 

//this will change the name and the key in collection too
Cart::item(1)->condition("all sale")->name("friday sale");

//now this item condition is accessible with the new key
Cart::item(1)->condition("friday sale");
```

##### Get name
- return : string

``` php
// CartCondition 

Cart::condition("all sale")->name;

// ItemCondition 

Cart::item(1)->condition("item sale")->name;
```

##### Set type
- type : string
- return : CartCondition | ItemCondition

``` php
// CartCondition 

Cart::condition("all sale")->type($type);

// ItemCondition 

Cart::item(1)->condition("item sale")->type($type);
```

##### Get type
- return : string

``` php
// CartCondition 

Cart::condition("all sale")->type;

// ItemCondition 

Cart::item(1)->condition("item sale")->type;
```

##### Set value
- value : string
- return : CartCondition | ItemCondition

``` php
// CartCondition 

Cart::condition("all sale")->value($value);

// ItemCondition 

Cart::item(1)->condition("item sale")->value($value);
```

##### Get value
- return : string

``` php
// CartCondition 

Cart::condition("all sale")->value;

// ItemCondition 

Cart::item(1)->condition("item sale")->value;
```

##### Set attributes
Merge existing attributes.
- attributes : array
- return : CartCondition | ItemCondition

``` php
// CartCondition 

$attributes = ["some_attribute" => "attribute"];
Cart::condition("all sale")->attributes($attributes);

// ItemCondition 

Cart::item(1)->condition("item sale")->attributes($attributes);
```

##### Get attributes
- return : ConditionAttributesCollection

``` php
// CartCondition 

Cart::condition("all sale")->attributes;
Cart::condition("all sale")->attributes->some_attribute;

// ItemCondition 

Cart::item(1)->condition("item sale")->attributes;
Cart::item(1)->condition("item sale")->attributes->some_attribute;

//or useing any LaravelCollection method
Cart::item(1)->condition("item sale")->attributes->get("some_attribute");
```

##### Empty attributes (delete all attributes)
- return : CartCondition | ItemCondition

``` php
// CartCondition 

Cart::condition("all sale")->emptyAttributes();

// ItemCondition 

Cart::item(1)->condition("item sale")->emptyAttributes();
```

#### Set helper
- [] : array - name : string (not required), type : string (not required), value : string (not required), attributes : array (not required)
- return : CartCondition | ItemCondition

``` php
// CartCondition 

Cart::condition("all sale")->set([
    "name" => "sale", 
    "type" => "sale", 
    "value" => "-10%",
    "attributes" => [
        "size" => "M"
    ]
]);

// ItemCondition 

Cart::item(1)->condition("all sale")->set([
    "name" => "sale", 
    "type" => "sale", 
    "value" => "-10%",
    "attributes" => [
        "size" => "M"
    ]
]);
```

## Testing

``` bash
$ composer test
```

## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md), [ISSUE_TEMPLATE](ISSUE_TEMPLATE.md), [PULL_REQUEST_TEMPLATE](PULL_REQUEST_TEMPLATE.md) and [CODE_OF_CONDUCT](CODE_OF_CONDUCT.md) for details.

## Security

If you discover any security related issues, please email lachezar@grigorov.website instead of using the issue tracker.

## Credits

- [Lachezar Grigorov](http://grigorov.website)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
