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

class ValidationResult
{
    /** @var bool */
    private $successful;

    /** @var string */
    private $message;

    /** @var string|null */
    private $tip;

    public function __construct(bool $successful, string $message, ?string $tip = null)
    {
        $this->successful = $successful;
        $this->message = $message;
        $this->tip = $tip;
    }

    public function isSuccessful(): bool
    {
        return $this->successful;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getTip(): ?string
    {
        return $this->tip;
    }
}
