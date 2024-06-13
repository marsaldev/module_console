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

use Exception;

interface OverriderInterface
{
    /**
     * Overrider execution.
     *
     * Creates the file(s), do the job.
     *
     * @throws Exception in case of hard fail. In case of soft fails just return message(s)
     *
     * @see isSuccessful()
     *
     * @return array<string> messages. Error or success messages depends on $this->isSuccessful()
     */
    public function run(): array;

    /**
     * Does the overrider handle this path ?
     *
     * @return bool
     */
    public function handle(): bool;

    /**
     * Was the execution successful ?
     * Response set by run().
     *
     * @return bool
     */
    public function isSuccessful(): bool;

    /**
     * Returns values expected :
     * - null : nothing bad can happen.
     * - string : description of what will happen if user confirms.
     *   This is probably a file overwrite.
     *   Message example : 'The file xxx will be overwritten.'
     *
     * If a string is returned the user will be prompted for confirmation (interactive mode only)
     * , unless force option is defined.
     * Otherwise, the overrider is not ran. Nothing happen.
     *
     * @todo Maybe returning an array is better : consistent with run(). But in fact, there will be only a single consequence...
     *
     * @return string|null
     */
    public function getDangerousConsequences(): ?string;

    public function init(string $path): void;
}
