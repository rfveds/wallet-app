<?php
/**
 * Tests for Calculator.
 */
namespace App\Tests\Util;

use App\Util\Calculator;
use PHPUnit\Framework\TestCase;

/**
 * Class CalculatorTest.
 */
class CalculatorTest extends TestCase
{
    /**
     * Test add() method.
     */
    public function testAdd()
    {
        $calculator = new Calculator();
        $result = $calculator->add(30, 12);

        // assert that your calculator added the numbers correctly!
        $this->assertEquals(42, $result);
    }
}
