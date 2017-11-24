<?php namespace Lachezargrigorov\Cart\Tests\Unit;

use Illuminate\Support\Collection;
use Lachezargrigorov\Cart\CartCondition;
use Lachezargrigorov\Cart\Collections\ItemAttributesCollection;
use Lachezargrigorov\Cart\Exceptions\CartException;
use Lachezargrigorov\Cart\Item;
use Lachezargrigorov\Cart\Traits\TestTrait;
use Tests\TestCase;


class CartTest extends TestCase
{
    use TestTrait;

    public function testItems()
    {
        $this->assertInstanceOf(Collection::class, $this->cart->items());
    }

    public function testConditions()
    {
        $this->assertInstanceOf(Collection::class, $this->cart->conditions());
    }

    public function testItem()
    {
        $this->cart->item(1)->quantity(1);
        $this->assertInstanceOf(Item::class, $this->cart->item(1));
    }

    public function testCondition()
    {
        $this->cart->condition("whole sale");
        $this->assertInstanceOf(CartCondition::class, $this->cart->condition("whole sale"));
    }

    public function testSetConditionAsArray()
    {
        $this->cart->setConditionAsArray($this->saleCondition);
        $this->assertTrue($this->cart->hasCondition("sale"));
        $this->cart->setConditionAsArray([$this->saleCondition,$this->bonusCondition]);
        $this->assertTrue($this->cart->hasCondition("sale"));
        $this->assertTrue($this->cart->hasCondition("bonus"));
    }

    public function testHas()
    {
        $this->cart->item(1)->quantity(1);
        $this->assertTrue($this->cart->has(1));
    }

    public function testHasCondition()
    {
        $this->cart->condition("whole sale");
        $this->assertTrue($this->cart->hasCondition("whole sale"));
    }


    public function testCount()
    {
        $this->cart->item(1)->quantity(1);
        $this->cart->item(2)->quantity(1);
        $this->assertEquals(2, $this->cart->count());
    }

    public function testCountConditions()
    {
        $this->cart->condition("whole sale1");
        $this->cart->condition("whole sale2");
        $this->assertEquals(2, $this->cart->countConditions());
    }

    public function testRemove()
    {
        $this->cart->item(1)->quantity(1);
        $this->cart->item(2)->quantity(1);
        $this->assertEquals(2, $this->cart->count());
        $this->cart->remove([1]);
        $this->assertEquals(1, $this->cart->count());
    }

    public function testEmpty()
    {
        $this->cart->item(1)->quantity(1);
        $this->cart->item(2)->quantity(1);
        $this->cart->empty();
        $this->assertEquals(0, $this->cart->count());
    }

    public function testRemoveConditions()
    {
        $this->cart->condition("whole sale1");
        $this->cart->condition("whole sale2");
        $this->assertEquals(2, $this->cart->countConditions());
        $this->cart->removeConditions(["whole sale1"]);
        $this->assertEquals(1, $this->cart->countConditions());
    }

    public function testEmptyConditions()
    {
        $this->cart->condition("whole sale1");
        $this->cart->condition("whole sale2");
        $this->cart->emptyConditions();
        $this->assertEquals(0, $this->cart->countConditions());
    }

    public function testIsEmpty()
    {
        $this->cart->item(1)->quantity(1);
        $this->assertFalse($this->cart->isEmpty(), 'Cart should not be empty');
        $this->cart->empty();
        $this->assertTrue($this->cart->isEmpty(), 'Cart should be empty');
    }

    public function isEmptyConditions()
    {
        $this->cart->condition("whole sale1");
        $this->assertFalse($this->cart->isEmptyConditions());
        $this->cart->clearConditions();
        $this->assertTrue($this->cart->isEmptyConditions());
    }

    public function testKeys()
    {
        $this->cart->item(1)->quantity(1);

        $this->assertTrue(in_array(1, $this->cart->keys()->toArray()));
    }

    public function testKeysOfConditions()
    {
        $this->cart->condition("whole sale");

        $this->assertTrue(in_array("whole sale", $this->cart->keysOfConditions()->toArray()));
    }

    public function testTotalQuantity()
    {
        $this->cart->item(1)->quantity(1);
        $this->cart->item(1)->addQuantity(1);
        $this->cart->item(2)->addQuantity(20);
        $this->assertEquals(22, $this->cart->totalQuantity());
        $this->cart->item(2)->addQuantity(10);
        $this->assertEquals(32, $this->cart->totalQuantity());
        $this->cart->item(2)->quantity(1);
        $this->assertEquals(3, $this->cart->totalQuantity());

    }

    public function testCartSubtotalAndTotal()
    {
        $this->cart->item(1)->set([
            "quantity" => 1,
            "attributes" => [
                "size" => "S",
            ],
            "conditions" => [
                [
                    "name"  => "whole sale",
                    "type"  => "all sale",
                    "value" => "10%",
                ], [
                    "name"  => "item sale",
                    "type"  => "item sale",
                    "value" => "-1",
                ]
            ]

        ]);

        $this->cart->item(2)->quantity(1);

        $this->cart->useModels($this->mockItems());

        // 5.24 + 10.48 = 15.72
        $this->assertEquals(15.72, $this->cart->subtotalWithoutConditions());

        // 5.24 * 10% = 5.764
        // 5.764 - 1 = 4.764
        // 4.764 + 10.48 = 15.244 ~ 15.24
        $this->assertEquals(15.24, $this->cart->subtotal());

        $this->cart->item(1)->setConditionAsArray([
            [
                "name"  => "whole sale",
                "type"  => "all sale",
                "value" => "-10%",
            ],
            [
                "name"  => "item sale",
                "type"  => "item sale",
                "value" => "+1",
            ],
        ]);



        // 5.24 * -10% = 5.764
        // 5.764 - 1 = 4.764
        // 5.716 + 10.48 = 16.196 ~ 16.2
        $this->assertEquals(16.20, $this->cart->subtotal());

        $this->cart->condition("sale")->type("sale")->value("-10%");

        // 16.20 - 1.62 (10%) = 14.58
        $this->assertEquals(14.58, $this->cart->total());

        $this->cart->condition("bonus")->type("bonus")->value("+2");

        //14.58 + 2 = 16.58
        $this->assertEquals(16.58, $this->cart->total());

    }

}