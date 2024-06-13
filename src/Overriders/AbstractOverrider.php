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

namespace MCM\Console\Overriders;

use LogicException;
use Symfony\Component\Filesystem\Filesystem;

abstract class AbstractOverrider implements OverriderInterface
{
    /** @var bool */
    private $successful = false;

    /**
     * @var ?string Path to override, provided as argument on command line
     *              e.g. classes/Cart.php , controllers/HomeController.php
     */
    private $path;

    /**
     * @var Filesystem
     */
    protected $fs;

    final public function setSuccessful(): void
    {
        $this->successful = true;
    }

    // not really needed since it's false by default.
    final public function setUnsuccessful(): void
    {
        $this->successful = false;
    }

    final public function isSuccessful(): bool
    {
        return $this->successful;
    }

    final public function init(string $path): void
    {
        $this->path = $path;
        $this->fs = new Filesystem();
    }

    /**
     * @return string source path to override
     */
    final public function getPath(): string
    {
        if (is_null($this->path)) {
            throw new LogicException('Overrider not initialized. Use ->init() before usage.');
        }
        if (!$this->fs->exists($this->path)) {
            throw new \RuntimeException("Path not found '{$this->path}'. Provide an existing file path to generate override.");
        }

        return $this->path;
    }
}
