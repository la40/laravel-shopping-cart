<?php namespace Lachezargrigorov\Cart\Tests\Unit;

use Illuminate\Support\Collection;
use Lachezargrigorov\Cart\Traits\TestTrait;
use Tests\TestCase;

class ConditionTest extends TestCase
{
    use TestTrait;

    public function testSet()
    {
        $condition = $this->cart->condition("sale")->set($this->saleCondition);
        $this->assertEquals("sale",$this->cart->condition("sale")->name);
        $this->assertEquals("sale",$this->cart->condition("sale")->type);
        $this->assertEquals("-10%",$this->cart->condition("sale")->value);
        $this->assertEquals("M",$this->cart->condition("sale")->attributes->size);
    }

    public function testNameAndNameSync()
    {
        $condition = $this->cart->condition("sale")->name("test name");
        $this->assertTrue($this->cart->hasCondition("test name"));

        $condition = $this->cart->item(1)->condition("sale")->name("test name");
        $this->assertTrue($this->cart->item(1)->hasCondition("test name"));
    }

    public function testType()
    {
        $condition = $this->cart->condition("sale")->type("test type");
        $this->assertEquals("test type",$this->cart->condition("sale")->type);
    }

    public function testValue()
    {
        $this->cart->condition("sale")->value("-20%");
        $this->assertEquals("-20%",$this->cart->condition("sale")->value);
    }

    public function testAttributes()
    {
        $condition = $this->cart->condition("sale")->attributes(["size" => "S"]);
        $this->assertEquals("S",$this->cart->condition("sale")->attributes->size);
    }

    public function testEmptyAttributes()
    {
        $condition = $this->cart->condition("sale")->attributes(["size" => "S"]);
        $this->cart->condition("sale")->emptyAttributes();
        $this->assertFalse($this->cart->condition("sale")->attributes->has("size"));
    }

    public function testApply()
    {
        $condition = $this->cart->condition("sale")->value("-20%");
        $this->assertEquals(0.96,$condition->apply(1.2));

        $condition = $this->cart->condition("sale")->value("+20%");
        $this->assertEquals(1.2,$condition->apply(1));

        $condition = $this->cart->condition("sale")->value("-0.5");
        $this->assertEquals(0.5,$condition->apply(1));

        $condition = $this->cart->condition("sale")->value("+0.5");
        $this->assertEquals(1.5,$condition->apply(1));
    }

    public function testRemove()
    {
        $this->cart->condition("sale");
        $this->assertTrue($this->cart->hasCondition("sale"));
        $this->cart->condition("sale")->remove();
        $this->assertFalse($this->cart->hasCondition("sale"));

        $this->cart->item(1)->condition("sale");
        $this->assertTrue($this->cart->item(1)->hasCondition("sale"));
        $this->cart->item(1)->condition("sale")->remove();
        $this->assertFalse($this->cart->item(1)->hasCondition("sale"));

    }
}
