<?php namespace Lachezargrigorov\Cart\Tests;

use Illuminate\Support\Collection;
use Lachezargrigorov\Cart\Tests\Unit\CartTest;
use Lachezargrigorov\Cart\Collections\ItemAttributesCollection;
use Lachezargrigorov\Cart\Exceptions\CartException;
use Lachezargrigorov\Cart\Traits\TestTrait;
use Tests\TestCase;

class ItemTest extends TestCase
{
    use TestTrait;

    public function testItemModel()
    {
        $this->cart->item(1)->quantity(1);
        $this->cart->useModels($this->mockItems());
        $this->assertEquals(1, $this->cart->item(1)->model->id);
    }

    public function testItemModelInterface()
    {
        $this->cart->item(1)->quantity(1);
        $this->cart->useModels($this->mockItems());
        $this->assertEquals(5.24, $this->cart->item(1)->model->getCartPrice());
    }

    public function testQuantity()
    {
        $this->cart->item(1)->quantity(2);
        $this->assertEquals(2,$this->cart->item(1)->quantity);
    }

    public function testAddQuantity()
    {
        $this->cart->item(1)->quantity(2);
        $this->cart->item(1)->addQuantity(2);
        $this->assertEquals(4,$this->cart->item(1)->quantity);
    }

    public function testAttributes()
    {
        $this->cart->item(1)->attributes(["size" => "S"]);
        $this->assertEquals("S",$this->cart->item(1)->attributes->size);
        $this->cart->item(1)->emptyAttributes();
        $this->assertFalse($this->cart->item(1)->attributes->has("size"));
    }

    public function testConditions()
    {
        $this->assertInstanceOf(Collection::class, $this->cart->item(1)->conditions());
    }

    public function testCondition()
    {
        $this->cart->item(1)->condition("sale");
        $this->assertTrue($this->cart->item(1)->hasCondition("sale"));
    }

    public function testisEmptyConditions()
    {
        $this->cart->item(1)->condition("sale");
        $this->assertTrue(!$this->cart->item(1)->isEmptyConditions());
    }

    public function testHasCondition()
    {
        $this->cart->item(1)->condition("sale");
        $this->assertTrue($this->cart->item(1)->hasCondition("sale"));
    }

    public function testEmptyCondition()
    {
        $this->cart->item(1)->condition("sale");
        $this->cart->item(1)->emptyConditions();
        $this->assertFalse($this->cart->item(1)->hasCondition("sale"));
    }

    public function testSetConditionsAsArray()
    {
        $this->cart->item(1)->setConditionAsArray($this->saleCondition);
        $this->assertTrue($this->cart->item(1)->hasCondition("sale"));
        $this->cart->item(1)->setConditionAsArray([$this->saleCondition,$this->bonusCondition]);
        $this->assertTrue($this->cart->item(1)->hasCondition("sale"));
        $this->assertTrue($this->cart->item(1)->hasCondition("bonus"));
    }


    public function testSet()
    {
        $this->cart->item(1)->set([
            "quantity" => 2,
            "attributes" => [
                "size" => "S"
            ],
            "conditions" => $this->saleCondition
        ]);

        $this->assertEquals(2,$this->cart->item(1)->quantity);
        $this->assertEquals("S",$this->cart->item(1)->attributes->size);
        $this->assertTrue($this->cart->item(1)->hasCondition("sale"));

        $this->cart->item(1)->set(["add_quantity" => 2]);
        $this->assertEquals(4,$this->cart->item(1)->quantity);

        $this->cart->item(1)->set(["quantity" => 1]);
        $this->assertEquals(1,$this->cart->item(1)->quantity);
    }

    public function testRemove()
    {
        $this->cart->item(1);
        $this->assertTrue($this->cart->has(1));
        $this->cart->item(1)->remove();
        $this->assertTrue($this->cart->isEmpty());

    }

    public function testDefaultItem()
    {
        $this->cart->item(1);
        $this->assertEquals(1,$this->cart->item(1)->id);
        $this->assertEquals(0,$this->cart->item(1)->quantity);
        $this->isInstanceOf(ItemAttributesCollection::class, $this->cart->item(1)->attributes);
        $this->isInstanceOf(Collection::class, $this->cart->item(1)->conditions);

    }

    public function testUpdate()
    {
        $this->cart->item(1)->set([
            "quantity" => 1,
            "attrubutes" => [
                "size"  => "M",
                "color" => "green",
            ],
            "conditions" => [
                "name"  => "whole sale",
                "type"  => "whole sale",
                "value" => "10%",
            ]
        ]);

        $this->assertFalse($this->cart->isEmpty(), 'Cart should not be empty');
        $this->assertEquals('whole sale', $this->cart->item(1)->conditions->get("whole sale")->type, 'Item should have condition with name whole sale and type whole sale');
        $this->assertEquals('10%', $this->cart->item(1)->conditions->get("whole sale")->value, 'Item should have condition with name whole sale and value 10%');
        $this->assertTrue(!$this->cart->item(1)->isEmptyConditions());
        $this->assertTrue($this->cart->item(1)->hasCondition("whole sale"));
        $this->assertInstanceOf(Collection::class, $this->cart->item(1)->conditions, 'Item should have conditions');

        $this->cart->item(1)->set([
            "attributes" => [
                "size" => "S",
            ],
            "conditions" => [
                "name"  => "whole sale",
                "type"  => "all sale",
                "value" => "10%",
            ]
        ]);

        $this->assertEquals(1, $this->cart->item(1)->quantity);
        $this->assertEquals('S', $this->cart->item(1)->attributes->size, 'Item should have attribute size of S');
        $this->assertEquals('all sale', $this->cart->item(1)->conditions->get("whole sale")->type, 'Item should have condition with name whole sale and type all sale');

        $this->cart->item(1)->set([
            "add_quantity" => 1,
            "attributes" => [
                "size"   => "L",
                "option" => "2",
            ],
            "conditions" => [
                [
                    "name" => "whole sale",
                    "type" => "sale",
                ],
                [
                    "name"  => "item sale",
                    "type"  => "item sale",
                    "value" => "5%",
                ],
            ]
        ]);
        $this->assertEquals('2', $this->cart->item(1)->quantity, 'Item quantity should be 2');
        $this->assertEquals('L', $this->cart->item(1)->attributes->size, 'Item should have attribute size of L');
        $this->assertEquals('2', $this->cart->item(1)->attributes->option, 'Item should have attribute option of 2');
        $this->assertEquals('sale', $this->cart->item(1)->conditions->get("whole sale")->type, 'Item should have condition with name whole sale and type sale');
        $this->assertEquals('10%', $this->cart->item(1)->conditions->get("whole sale")->value, 'Item should have condition with name whole sale and value 10%');
        $this->assertEquals('5%', $this->cart->item(1)->conditions->get("item sale")->value, 'Item should have condition with name item sale and value 5%');

        $this->cart->item(1)->quantity(20);
        $this->assertEquals(20, $this->cart->item(1)->quantity);
    }

    public function testPriceMethods()
    {
        $this->cart->item(1)->quantity(2)->condition("whole sale")->type("all sale")->value("-2");

        $this->cart->useModels($this->mockItems(1));

        $this->assertEquals(5.24,$this->cart->item(1)->priceWithoutConditions());
        // 5.24 * 2 = 10.48
        $this->assertEquals(10.48,$this->cart->item(1)->priceSumWithoutConditions());

        // 5.24 - 2 = 3.24
        $this->assertEquals(3.24,$this->cart->item(1)->price());

        // 3.24 * 2 = 6.48
        $this->assertEquals(6.48,$this->cart->item(1)->priceSum());


    }
}
