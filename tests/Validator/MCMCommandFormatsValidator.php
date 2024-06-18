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

declare(strict_types=1);

namespace MCM\Console\Tests\Validator;

use Exception;

/**
 * Class MCMCommandFormatsValidator
 * Rules :
 * - FQCN must follow pattern : MCM\Console\Commands\<Domain>\<Domain><Action>[<Subaction>]Command
 *   - <Domain> is not empty
 *   - <Action> is not empty
 *   - <Subaction> can be empty
 * - command name (symfony command name) is consistent with <Domain> and <Action>
 * - service name (symfony service declaration) is consistent with <Domain> and <Action>
 */
class MCMCommandFormatsValidator
{
    /**
     * @var string Regular expression for command's fully qualified class name
     *             `MCM\Console\Commands\Domain\DomainAction[SubAction]Command`
     */
    private const FQCN_REGEXP = '#^MCM\\\Console\\\Commands\\\(?<domain>[[:alpha:]]+)\\\(?<action>[[:alpha:]]+)$#X';
//    private const FQCN_REGEXP = '#^MCM\\\Console\\\Commands\\\(?<domain>[[:alpha:]]+)\\\(?<action>[[:alpha:]]+)(?:\\\(?<subaction>[[:alpha:]]*))?$#X';

    /**
     * @var string regular expression for command's name
     *             `mcm:domain:action` or `mcm:domain:action:subaction`
     *             action/subaction and domain can contain '-'
     */
    private const COMMAND_REGEXP = '#^mcm:(?<domain>[[:alpha:]-]+):(?<action>[[:alpha:]-]+)(?::(?<subaction>[[:alpha:]-]+))?$#X';

    /**
     * @var string
     *             Regular expression used to split <domain> and <action> to words in COMMAND_REGEXP
     */
    private const COMMAND_SPLIT_WORDS_REGEXP = '/[-]/';

    /**
     * @var string regular expression for service's name
     *             `mcm.console.domain.action.command`
     *             action and domain can contain '_'
     */
    private const SERVICE_REGEXP = '#^mcm\.console\.(?<domain>[[:alpha:]_]+)\.(?<action>[[:alpha:]_]+)\.command$#X';

    /**
     * @var string
     *             Regular expression used to split <domain> and <action> to words in SERVICE_REGEXP
     */
    private const SERVICE_SPLIT_WORDS_REGEXP = '/[_]/';

    /** @var ValidationResults */
    private $results;

    /**
     * @param string $fullyQualifiedClassName php class name, e.g. ModuleHooks
     * @param string $commandName symfony command name, e.g. mcm:modules:hooks
     * @param string $service service name defined in config/services.yml. e.g. mcm.console.modules.module_hooks.command
     *
     * @return ValidationResults
     */
    public function validate(
        string $fullyQualifiedClassName,
        string $commandName,
        string $service
    ): ValidationResults {
        $this->results = new ValidationResults();
        $fullyQualifiedClassName = preg_replace('/Command$/', '', $fullyQualifiedClassName);

        $this->checkDomainIsNotEmptyInClassName($fullyQualifiedClassName);
        $this->checkActionIsNotEmptyInClassName($fullyQualifiedClassName);
        $this->checkDomainIsRepeatedInActionInClassName($fullyQualifiedClassName);
        $this->checkCommandNameIsConsistentWithClassName($commandName, $fullyQualifiedClassName);
        $this->checkServiceNameIsConsistentWithClassName($service, $fullyQualifiedClassName);

        return $this->results;
    }

    private function checkDomainIsNotEmptyInClassName(string $fullyQualifiedClassName): void
    {
        $domain = $this->extractDomainFromFQCN($fullyQualifiedClassName);
        if (empty($domain)) {
            $this->results->addResult(new ValidationResult(false, "Domain can't be empty."));

            return;
        }
        $this->results->addResult(new ValidationResult(true, 'Domain found in FQCN.'));
    }

    private function checkActionIsNotEmptyInClassName(string $fullyQualifiedClassName): void
    {
        $action = $this->extractActionFromFQCN($fullyQualifiedClassName);

        if (empty($action)) {
            $this->results->addResult(new ValidationResult(false, "Action can't be empty."));

            return;
        }
        $this->results->addResult(new ValidationResult(true, 'Action found in FQCN.'));
    }

    private function checkDomainIsRepeatedInActionInClassName(string $fullyQualifiedClassName): void
    {
        $action = $this->extractActionFromFQCN($fullyQualifiedClassName);
        $domain = $this->extractDomainFromFQCN($fullyQualifiedClassName);

        // emptiness must be checked before processing strpos(), strpos() doesn't support empty needle
        if (empty($domain) || strpos($action, $domain) !== 0) {
            $this->results->addResult(new ValidationResult(false, "Domain '$domain' must be included in command class name."));

            return;
        }
        $this->results->addResult(new ValidationResult(true, 'Domain is repeated in Action part of FQCN.'));
    }

    /**
     * Logic : extract domain and action from fqcn and compare with domain and action extracted from command name
     *
     * @param string $commandName
     * @param string $fullyQualifiedClassName
     *
     * @return void
     */
    private function checkCommandNameIsConsistentWithClassName(
        string $commandName,
        string $fullyQualifiedClassName
    ): void {
        list($domainWords, $actionWords, $subActionWords) = $this->extractDomainAndActionsAndSubActionsFromRegexp(self::COMMAND_REGEXP, self::COMMAND_SPLIT_WORDS_REGEXP, $commandName);
        $actionWordsFromFQCN = $this->getWordsFromCamelCasedString($this->extractActionWithoutDomainFromFQCN($fullyQualifiedClassName));
        // $subActionWordsFromFQCN = $this->getWordsFromCamelCasedString($this->extractSubactionWithoutDomainFromFQCN($fullyQualifiedClassName));
        $domainWordsFromFQCN = $this->getWordsFromCamelCasedString($this->extractDomainFromFQCN($fullyQualifiedClassName));

        if ($domainWords != $domainWordsFromFQCN || $actionWords != $actionWordsFromFQCN) {
            $this->results->addResult(
                new ValidationResult(
                    false,
                    "Wrong command name '$commandName'. Fix domain or action pattern"
                )
            );

            return;
        }

        /*if ($subActionWords && $subActionWords != $subActionWordsFromFQCN) {
            $this->results->addResult(
                new ValidationResult(
                    false,
                    "Wrong command name '$commandName'. Fix subaction pattern"
                )
            );

            return;
        }*/
        $this->results->addResult(new ValidationResult(true, 'Symfony command name is consistent with FQCN.'));
    }

    /**
     * Check Service Name Is Consistent With Class Name
     * Logic : rebuild the fcqn from the service name then compare.
     *
     * @param string $service
     * @param string $fullyQualifiedClassName
     *
     * @return void
     *
     * @throws \Exception
     */
    private function checkServiceNameIsConsistentWithClassName(
        string $service,
        string $fullyQualifiedClassName
    ): void {
        list($domainWords, $actionWords) = $this->extractDomainAndActionsAndSubActionsFromRegexp(self::SERVICE_REGEXP, self::SERVICE_SPLIT_WORDS_REGEXP, $service);
        $actionWordsFromFQCN = $this->getWordsFromCamelCasedString($this->extractActionWithoutDomainFromFQCN($fullyQualifiedClassName));
        $domainWordsFromFQCN = $this->getWordsFromCamelCasedString($this->extractDomainFromFQCN($fullyQualifiedClassName));

        if ($domainWords != $domainWordsFromFQCN || $actionWords != $actionWordsFromFQCN) {
            $this->results->addResult(
                new ValidationResult(
                    false,
                    "Wrong service name '$service'"
                )
            );

            return;
        }
        $this->results->addResult(new ValidationResult(true, 'Service name is consistent with FQCN.'));
    }

    /**
     * Split string on each Capitalized letter.
     *
     * No need to capitalize the first letter.
     * HelloWorld => ['Hello', 'World']
     *
     * @param string $subject
     *
     * @return array<string>
     */
    private function getWordsFromCamelCasedString(string $subject): array
    {
        return preg_split('/(?=[A-Z])/', ucfirst($subject), -1, PREG_SPLIT_NO_EMPTY) ?: [''];
    }

    /**
     * @return string domain in CamelCase format `Domain`
     */
    private function extractDomainFromFQCN(string $fullyQualifiedClassName): string
    {
        return $this->getFQCNRegexpMatches($fullyQualifiedClassName)['domain'] ?? '';
    }

    /**
     * @return string domain+action in CamelCase format `DomainAction`
     */
    private function extractActionFromFQCN(string $fullyQualifiedClassName): string
    {
        return $this->getFQCNRegexpMatches($fullyQualifiedClassName)['action'] ?? '';
    }

    /*private function extractSubActionFromFQCN(string $fullyQualifiedClassName): string
    {
        return (isset($this->getFQCNRegexpMatches($fullyQualifiedClassName)['subaction'])) ? $this->getFQCNRegexpMatches($fullyQualifiedClassName)['subaction'] : '';
    }*/

    /**
     * @return string action in CamelCase format `Action`
     */
    private function extractActionWithoutDomainFromFQCN(string $fullyQualifiedClassName): string
    {
        return str_replace(
            $this->extractDomainFromFQCN($fullyQualifiedClassName),
            '',
            $this->extractActionFromFQCN($fullyQualifiedClassName)
        );
    }

    /*private function extractSubactionWithoutDomainFromFQCN(string $fullyQualifiedClassName): string
    {
        return str_replace(
            [
                $this->extractDomainFromFQCN($fullyQualifiedClassName),
                $this->extractActionFromFQCN($fullyQualifiedClassName),
            ],
            '',
            $this->extractSubActionFromFQCN($fullyQualifiedClassName)
        );
    }*/

    /**
     * @param string $fullyQualifiedClassName
     *
     * @return array{domain?: string, action?: string}
     */
    private function getFQCNRegexpMatches(string $fullyQualifiedClassName): array
    {
        preg_match(self::FQCN_REGEXP, $fullyQualifiedClassName, $matches);

        return $matches ?: [];
    }

    /**
     * @param string $regexp
     * @param string $splitWordsRegexp
     * @param string $subject
     *
     * @return array{array<int, string>, array<int, string>, array<int, string>}
     *
     * @throws \Exception
     */
    private function extractDomainAndActionsAndSubActionsFromRegexp(string $regexp, string $splitWordsRegexp, string $subject): array
    {
        preg_match($regexp, $subject, $matches);
        $domainWords = preg_split($splitWordsRegexp, $matches['domain'] ?? '');
        if (false === $domainWords) {
            throw new Exception("failed to extract domain words from '$subject'.");
        }
        $domainWords = array_map('ucfirst', $domainWords);

        // action words : string split in words using `-` or `:` as separator then CamelCased
        $actionWords = preg_split($splitWordsRegexp, $matches['action'] ?? '');
        if (false === $actionWords) {
            throw new Exception("failed to extract action words from '$subject'.");
        }
        $actionWords = array_map('ucfirst', $actionWords);

        // subaction words : string split in words using `-` or `:` as separator then CamelCased
        $subactionWords = preg_split($splitWordsRegexp, $matches['subaction'] ?? '');
        if (false === $subactionWords) {
            throw new Exception("failed to extract subaction words from '$subject'.");
        }
        $subactionWords = array_map('ucfirst', $subactionWords);

        return [$domainWords, $actionWords, $subactionWords];
    }
}
