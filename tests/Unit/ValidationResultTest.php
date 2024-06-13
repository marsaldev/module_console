<?php
/**
 * Copyleft (c) Since 2024 Marco Salvatore
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file docs/licenses/LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/afl-3.0.php
 *
 * @author    Marco Salvatore <hi@marcosalvatore.dev>
 * @copyleft since 2024 Marco Salvatore
 * @license   https://opensource.org/licenses/AFL-3.0  Academic Free License ("AFL") v. 3.0
 *
 */

namespace MCM\Console\Tests\Unit;

use MCM\Console\Tests\Validator\ValidationResult;
use PHPUnit\Framework\TestCase;

class ValidationResultTest extends TestCase
{
    public function testItsConstructedWith3Parameters()
    {
        $result = new ValidationResult(false, 'something went wrong.', 'do this to fix it.');
        $this->assertTrue(is_object($result));
    }

    public function testIsSuccessfulTrue()
    {
        $result = new ValidationResult(true, 'something went wrong.', 'do this to fix it.');
        $this->assertTrue($result->isSuccessful());
    }

    public function testIsSuccessfulFalse()
    {
        $result = new ValidationResult(false, 'something went wrong.', 'do this to fix it.');
        $this->assertFalse($result->isSuccessful());
    }

    public function testHasMessageGetter()
    {
        $message = 'something went wrong.';
        $result = new ValidationResult(false, $message, 'do this to fix it.');
        $this->assertEquals($message, $result->getMessage());
    }

    public function testHasTipGetter()
    {
        $tip = 'this is a tip.';
        $result = new ValidationResult(false, 'a message', $tip);
        $this->assertEquals($tip, $result->getTip());
    }
}
