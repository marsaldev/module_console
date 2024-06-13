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

namespace MCM\Console\Overriders;

use Exception;

class Provider
{
    /**
     * @var array<OverriderInterface>
     */
    private $overriders;

    /**
     * Provider constructor.
     *
     * @param array<OverriderInterface> $overriders
     *
     * @throws Exception
     */
    public function __construct(array $overriders)
    {
        // check that provided Overriders are really Overriders.
        array_walk($overriders, function ($overrider) {
            if (!$overrider instanceof OverriderInterface) {
                throw new Exception(__CLASS__ . ' parameter $overrider must contain ' . OverriderInterface::class . ' instances only.');
            }
        });
        $this->overriders = $overriders;
    }

    /**
     * Returns the overriders which handle this path.
     *
     * @param string $path
     *
     * @return OverriderInterface[]
     */
    public function getOverriders(string $path): array
    {
        $overriders = $this->overriders;
        // initialize overriders
        array_walk(
            $overriders,
            function (OverriderInterface $overrider) use ($path) {
                $overrider->init($path);
            }
        );
        // just keep overriders that handle this path
        $overriders = array_filter($overriders, function ($overrider) {
            return $overrider->handle();
        });

        return $overriders;
    }
}
