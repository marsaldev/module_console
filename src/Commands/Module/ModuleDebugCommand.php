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

namespace MCM\Console\Commands\Module;

use MCM\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class ModuleDebugCommand extends ModuleGenerate
{
    private array $allowedActions = [
        'generate',
        'remove',
    ];

    private $allowedMethods = [
        'main.php',
        'composer.json',
        'services.yml',
        'routes.yml',
        'controller',
        'type',
        'data.configuration',
        'form.data.provider',
        'controller.view',
    ];

    private $methodsNeedsNamespace = [
        'composer.json',
        'services.yml',
        'routes.yml',
        'controller',
    ];

    private $methodsNeedsAuthor = [
        'main.php',
        'composer.json',
        'services.yml',
        'routes.yml',
        'controller',
        'type',
        'data.configuration',
        'form.data.provider',
    ];

    protected function configure(): void
    {
        $this->setName('mcm:module:debug')
            ->addArgument('action', InputArgument::REQUIRED)
            ->addArgument('file', InputArgument::REQUIRED)
            ->addArgument('module', InputArgument::REQUIRED)
            ->addArgument('args', InputArgument::IS_ARRAY);
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');

        $ask_module_author = new Question('Please enter the name of the module author: ', 'Linus Torvald');
        $ask_namespace = new Question('Please enter the name space (ex Test\Module): ', 'Test\Module');

        if (in_array($input->getArgument('file'), $this->methodsNeedsAuthor)) {
            $this->moduleAuthor = $helper->ask($input, $output, $ask_module_author);
        }

        if (in_array($input->getArgument('file'), $this->methodsNeedsNamespace)) {
            $this->moduleNamespace = $helper->ask($input, $output, $ask_namespace);
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->moduleName = $input->getArgument('module');
        $action = $input->getArgument('action');
        $file = $input->getArgument('file');

        if (!file_exists($this->getModuleDirectory($this->moduleName))) {
            $this->displayMessage(
                $this->translator->trans(
                    'Unknown module %moduleName%. Check if the directory exists.',
                    ['%moduleName%' => $this->moduleName],
                    'Admin.Modules.Notification'
                ),
                'error'
            );

            return Command::FAILURE;
        }

        if (!in_array($action, $this->allowedActions)) {
            $this->displayMessage(
                $this->translator->trans(
                    'Unknown action. It must be one of these values: %actions%',
                    ['%actions%' => implode(' / ', $this->allowedActions)],
                    'Admin.Modules.Notification'
                ),
                'error'
            );

            return Command::FAILURE;
        }

        if (!in_array($file, $this->allowedMethods)) {
            $this->displayMessage(
                $this->translator->trans(
                    'Unknown method. It must be one of these values: %methods%',
                    ['%methods%' => implode(' / ', $this->allowedMethods)],
                    'Admin.Modules.Notification'
                ),
                'error'
            );

            return Command::FAILURE;
        }

        $this->twig = $this->getContainer()->get('twig');

        switch ($action) {
            case 'generate':
                switch ($file) {
                    case 'main.php':
                        $this->createMainModuleFile($this->moduleName, $this->moduleAuthor);
                        break;
                    case 'composer.json':
                        $this->createComposerJson($this->moduleName, $this->moduleAuthor, $this->moduleNamespace);
                        break;
                    case 'services.yml':
                        $this->createConfig($this->moduleName, $this->moduleAuthor, $this->moduleNamespace, 'Configuration');
                        break;
                    case 'routes.yml':
                        $this->createRoute($this->moduleName, $this->moduleAuthor, $this->moduleNamespace);
                        break;
                    case 'controller':
                        $this->createController($this->moduleName, $this->moduleAuthor, $this->moduleNamespace);
                        break;
                    case 'type':
                        $this->createConfigurationType($this->moduleName, $this->moduleAuthor);
                        break;
                    case 'data.configuration':
                        $this->createConfigurationDataConfiguration($this->moduleName, $this->moduleAuthor);
                        break;
                    case 'form.data.provider':
                        $this->createConfigurationFormDataProvider($this->moduleName, $this->moduleAuthor);
                        break;
                    case 'controller.view':
                        $this->createControllerTemplate($this->moduleName);
                        break;
                    default:
                        break;
                }
                break;
            case 'remove':
                break;
            default:
                break;
        }

        return Command::SUCCESS;
    }
}
