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

namespace MCM\Console;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Symfony Command with legacy support.
 */
abstract class Command extends ContainerAwareCommand
{
    public const SUCCESS = 0;
    public const FAILURE = 1;
    public const INVALID = 2;

    /** @var \Symfony\Component\Console\Style\SymfonyStyle */
    protected $io;

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $container = $this->getContainer();
        $commandDefinition = $this->getDefinition();
        $commandDefinition->addOption(new InputOption('employee', '-em', InputOption::VALUE_REQUIRED, 'Specify employee context (id).', null));

        $container->get('mcm.console.console_loader')->loadConsoleContext($input);

        $this->io = new SymfonyStyle($input, $output);

        if (isset($_SERVER['argv']) && count($_SERVER['argv']) > 1
            && in_array($_SERVER['argv'][1], $this->getAliases())
        ) {
            $this->io->warning("This command has a new name : {$this->getName()}. The alias you entered is deprecated and will be deleted in version 2.");
        }

        parent::initialize($input, $output);
    }
}
