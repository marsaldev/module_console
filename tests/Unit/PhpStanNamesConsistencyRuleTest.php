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

use MCM\Console\DevTools\PhpStanNamesConsistencyRule;
use MCM\Console\Tests\Validator\PhpStanNamesConsistencyService;
use MCM\Console\Tests\Validator\ValidationResult;
use MCM\Console\Tests\Validator\ValidationResults;
use PHPStan\Testing\RuleTestCase;

class PhpStanNamesConsistencyRuleTest extends RuleTestCase
{
    private const FAKE_ERROR_MESSAGE_1 = 'ĝi estas eraro.';
    private const FAKE_ERROR_MESSAGE_2 = 'tio estas alia eraro.';

    /**
     * The phpstan rule, constructed with a custom services files.
     *
     * @return \PHPStan\Rules\Rule
     */
    protected function getRule(): \PHPStan\Rules\Rule
    {
        // we are testing the phpstan rule, not the validation injected.
        // so we mock it.
        $mockedValidatorService = $this->createMock(PhpStanNamesConsistencyService::class);

        $validationResults = new ValidationResults();
        $validationResults->addResult(new ValidationResult(false, self::FAKE_ERROR_MESSAGE_1));
        $validationResults->addResult(new ValidationResult(true, 'tio estas bona.'));
        $validationResults->addResult(new ValidationResult(false, self::FAKE_ERROR_MESSAGE_2));

        $mockedValidatorService->method('validateNames')->willReturn($validationResults);

        // we can't use the original class, so we use an extended class.
        // this is because PhpStanNamesConsistencyRule::nodeIsInClassFopCommand() checks the FQDN and therefore it needs to be changed
        // with the namespace of the tested class (MCM\Console\Tests\Resources\Commands\Domain).
        /* @phpstan-ignore-next-line */
        return new class($mockedValidatorService) extends PhpStanNamesConsistencyRule {
            public const MCM_BASE_COMMAND_CLASS_NAME = 'MCM\Console\Tests\Resources\Commands\Command';
        };
    }

    /**
     * This test tests :
     * - that validation happens on the correct line - line number of method "configure" declaration.
     * - the message are the failures returned by PhpStanNamesConsistencyService
     *
     * @return void
     */
    public function testRule(): void
    {
        // first argument: path to the example file that contains some errors that should be reported by MyRule
        // second argument: an array of expected errors,
        // each error consists of the asserted error message, and the asserted error file line
        $this->analyse(
            [
                __DIR__ . '/../Resources/Commands/Command.php',
                __DIR__ . '/../Resources/Commands/Domain/DomainAction.php',
            ],
            [
            [
                self::FAKE_ERROR_MESSAGE_1, // asserted error message
                29, // asserted error line -
            ],
            [
                self::FAKE_ERROR_MESSAGE_2,
                29,
            ],
        ]
        );
    }
}
