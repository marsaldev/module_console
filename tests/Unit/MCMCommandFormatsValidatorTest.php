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

use MCM\Console\Tests\Validator\MCMCommandFormatsValidator;
use MCM\Console\Tests\Validator\ValidationResult;
use MCM\Console\Tests\Validator\ValidationResults;
use PHPUnit\Framework\TestCase;

class MCMCommandFormatsValidatorTest extends TestCase
{
    /** @var MCMCommandFormatsValidator */
    private $validator;

    protected function setUp(): void
    {
        $this->validator = new MCMCommandFormatsValidator();
    }

    public function testValidateReturnsInstanceOfValidationResults()
    {
        $this->assertInstanceOf(
            ValidationResults::class,
            $this->validator->validate('fqcn', 'command:name', 'fop.service')
        );
    }

    /**
     * @dataProvider commandsFormatsProvider
     */
    public function testValidate($commandFQCN, $commandName, $commandService, $expected)
    {
        $results = $this->validator->validate(
            $commandFQCN,
            $commandName,
            $commandService
        );

        $successful = $results->isValidationSuccessful();
        $messages = array_reduce($results->getFailures(), function ($messages, ValidationResult $result) {
            return $messages . $result->getMessage() . PHP_EOL;
        }, '');

        $this->assertSame(
            filter_var($expected, FILTER_VALIDATE_BOOLEAN),
            $successful,
            $messages
        );
    }

    /**
     * @dataProvider commandsFormatsProviderRealWorld
     */
    public function testValidateCurrents($commandFQCN, $commandName, $commandService, $expected)
    {
        $this->testValidate($commandFQCN, $commandName, $commandService, $expected);
    }

    public function commandsFormatsProvider(): CSVFileIterator
    {
        return new CSVFileIterator('tests/Resources/commands-formats.csv');
    }

    public function commandsFormatsProviderRealWorld(): CSVFileIterator
    {
        return new CSVFileIterator('tests/Resources/commands-realworld.csv');
    }
}
