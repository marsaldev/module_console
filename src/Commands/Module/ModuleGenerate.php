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

namespace MCM\Console\Commands\Module;

use MCM\Console\Command;
use MCM\Console\Tools\FindAndReplaceTool;
use PrestaShopBundle\Translation\Translator;
use Symfony\Component\Console\Helper\FormatterHelper;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Filesystem\Filesystem;

class ModuleGenerate extends Command
{
    public const MODULE_NAME = 'module_console_manager';

    protected $filesystem;
    protected $baseControllerFolder;
    protected $baseTestFolder;
    protected $baseFolder;
    protected $moduleName;
    protected $moduleNamespace;
    protected $frontControllerName;
    protected $isNewModule = false;
    protected $baseViewFolder;
    protected $testGeneration = false;
    protected $twig;

    protected $findAndReplaceTool;
    protected $moduleAuthor;
    protected $description;
    protected $website;
    protected $license;
    protected $email;
    protected string $baseAdminConfigFolder;
    protected string $baseFormFolder;
    protected string $baseTypeFolder;

    /** @var ProgressBar */
    protected $creationProgressBar;
    protected string $baseConfigFolder;
    protected string $baseTemplatesFolder;
    protected InputInterface $input;
    protected OutputInterface $output;
    /**
     * @var FormatterHelper
     */
    protected $formatter;
    /**
     * @var Translator
     */
    protected $translator;

    public function __construct()
    {
        $this->filesystem = new Filesystem();
        $this->baseFolder = _PS_MODULE_DIR_ . self::MODULE_NAME . DIRECTORY_SEPARATOR . 'src/Resources/templates/generate_module_command/module';
        $this->baseConfigFolder = $this->baseFolder . DIRECTORY_SEPARATOR . 'config';
        $this->baseAdminConfigFolder = $this->baseFolder . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'admin';
        $this->baseFormFolder = $this->baseFolder . DIRECTORY_SEPARATOR . 'form';
        $this->baseTypeFolder = $this->baseFolder . DIRECTORY_SEPARATOR . 'type';
        $this->baseControllerFolder = $this->baseFolder . DIRECTORY_SEPARATOR . 'controller';
        $this->baseViewFolder = $this->baseFolder . DIRECTORY_SEPARATOR . 'views';
        $this->baseTemplatesFolder = $this->baseViewFolder . DIRECTORY_SEPARATOR . 'templates';
        $this->baseTestFolder = $this->baseFolder . DIRECTORY_SEPARATOR . 'test';
        $this->findAndReplaceTool = new FindAndReplaceTool();

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this->setName('mcm:module:generate')
            ->setAliases(['module:generate', 'module_console_manager:module:generate'])
            ->setDescription('Scaffold new PrestaShop module');
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->formatter = $this->getHelper('formatter');
        $this->translator = $this->getContainer()->get('translator');
        $this->input = $input;
        $this->output = $output;
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');

        $ask_module_name = new Question('Please enter the name of the module (ex. testmodule): ', 'testmodule');
        $ask_module_author = new Question('Please enter the name of the module author: ', 'Linus Torvald');
        $ask_author_email = new Question('Please enter the email of the author: ', 'author@gmail.com');

        $ask_author_website_insertion = new Question('Do you want to add the website of the author? [yes/no] [default=no]: ', 'no');
        $ask_author_website = new Question('Please enter the website url of the module\'s author: [without https://]');

        $ask_module_description_yes = new Question('Do you want to add the description of the module? [yes/no] [default=no]: ', 'no');
        $ask_module_description = new Question('Please enter the description of the module: ', 'Description');

        $ask_module_license_insertion = new Question('Do you want to add the licence of the module? [yes/no] [default=no]: ', 'no');
        $ask_module_license = new Question('Please enter the licence specification of the module: ', 'AFL-3.0');

        $ask_namespace = new Question('Please enter the name space (ex Test\Module): ', 'Test\Module');
        $ask_front_controller = new Question('You need add a front controller? [yes/no] [default=no]: ', 'no');
        $ask_front_controller_name = new Question('What\'s the name of the front controller? [yes/no] [default=no]: ', 'no');

        $ask_phpunit_generation = new Question('You want to add tests? [yes/no] [default=no]: ', 'no');

        $this->moduleName = $this->findAndReplaceTool->sanitizeWords($helper->ask($input, $output, $ask_module_name));
        $this->isNewModule = !file_exists($this->getModuleDirectory($this->moduleName));

        if ($this->isNewModule === true) {
            $this->moduleNamespace = $helper->ask($input, $output, $ask_namespace);

            $this->moduleAuthor = $helper->ask($input, $output, $ask_module_author);
            $this->email = $helper->ask($input, $output, $ask_author_email);

            if ($helper->ask($input, $output, $ask_author_website_insertion) === 'yes') {
                $this->website = 'https://' . $helper->ask($input, $output, $ask_author_website);
            }
            if ($helper->ask($input, $output, $ask_module_description_yes) === 'yes') {
                $this->description = $helper->ask($input, $output, $ask_module_description);
            }
            if ($helper->ask($input, $output, $ask_module_license_insertion) === 'yes') {
                $this->description = $helper->ask($input, $output, $ask_module_license);
            }

            $this->testGeneration = $helper->ask($input, $output, $ask_phpunit_generation) === 'yes';
        }

        if ($helper->ask($input, $output, $ask_front_controller) === 'yes') {
            $this->frontControllerName = $helper->ask($input, $output, $ask_front_controller_name);
        }
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int|void|null
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->twig = $this->getContainer()
            ->get('twig');

        if ($this->isNewModule === true) {
            $progressBarUnits = 9;
            if ($this->testGeneration === true) {
                ++$progressBarUnits;
            }
            // creates a new progress bar
            $this->creationProgressBar = new ProgressBar($output, $progressBarUnits);
            $this->creationProgressBar->setFormat('%message% %current%/%max% [%bar%] %percent:3s%%');

            $this->output->writeln('');

            $this->creationProgressBar->setMessage('<info>Creating module folder...</info>');
            $this->createModuleFolder($this->moduleName);
            $this->creationProgressBar->start(); // starts and displays the progress bar
            $this->creationProgressBar->advance();

            $this->creationProgressBar->setMessage('<info>Creating main file</info>');
            $this->createMainModuleFile($this->moduleName, $this->moduleAuthor);
            $this->creationProgressBar->advance();

            $this->creationProgressBar->setMessage('<info>Creating composer.json</info>');
            $this->createComposerJson(
                $this->moduleName,
                $this->moduleAuthor,
                $this->moduleNamespace,
                $this->email,
                $this->license,
                $this->description,
                $this->website,
            );
            $this->creationProgressBar->advance();

            $this->creationProgressBar->setMessage('<info>Creating services.yml</info>');
            $this->createConfig(
                $this->moduleName,
                $this->moduleAuthor,
                $this->moduleNamespace,
                'Configuration'
            );
            $this->creationProgressBar->advance();

            // $output->writeln(' ');
            $this->creationProgressBar->setMessage('<info>Creating routes.yml</info>');
            $this->createRoute(
                $this->moduleName,
                $this->moduleAuthor,
                $this->moduleNamespace,
            );
            $this->creationProgressBar->advance();

            $this->creationProgressBar->setMessage('<info>Creating configuration controller...</info>');
            $this->createController($this->moduleName, $this->moduleAuthor, $this->moduleNamespace);
            $this->creationProgressBar->advance();

            $this->creationProgressBar->setMessage('<info>Creating Configuration Type...</info>');
            $this->createConfigurationType($this->moduleName, $this->moduleNamespace);
            $this->creationProgressBar->advance();

            $this->creationProgressBar->setMessage('<info>Creating Configuration Data Configuration...</info>');
            $this->createConfigurationDataConfiguration($this->moduleName, $this->moduleNamespace);
            $this->creationProgressBar->advance();

            $this->creationProgressBar->setMessage('<info>Creating Configuration Form Data Provider...</info>');
            $this->createConfigurationFormDataProvider($this->moduleName, $this->moduleNamespace);
            $this->creationProgressBar->advance();

            /*
            $output->writeln('<info>Creating Configuration Form...</info>');
            $this->createControllerForm($this->moduleName, $this->moduleNamespace);
            */

            $this->creationProgressBar->setMessage('<info>Creating configuration controller template...</info>');
            $this->createControllerTemplate($this->moduleName);
            $this->testGeneration ? $this->creationProgressBar->advance() : $this->creationProgressBar->finish();

            if ($this->testGeneration === true) {
                $this->creationProgressBar->setMessage('<info>create test folder</info>');
                $this->createTest($this->moduleName);
                $this->creationProgressBar->finish();
            }

            $output->writeln('');
            $output->writeln('');
            $output->writeln('<info>DONE!</info>');
            $output->writeln('<comment>Remember to check (and edit if necessary) the composer.json and run "composer install" inside your new module before install it in PrestaShop.</comment>');
            $output->writeln('');
        } else {
            $progressBarUnits = 0;
            if ($this->frontControllerName === true) {
                $progressBarUnits = 1;
            }
            // creates a new progress bar (9 units)
            $this->creationProgressBar = new ProgressBar($output, $progressBarUnits);
            // starts and displays the progress bar
            $this->creationProgressBar->start();

            if ($this->frontControllerName) {
                $output->writeln('<info>Creating front controller file...</info>');
                $this->createFrontController($this->moduleName, $this->frontControllerName);
                $this->creationProgressBar->advance();

                $output->writeln('<info>Creating front javascript file...</info>');
                $this->createFrontControllerJavascript($this->moduleName, $this->frontControllerName);
                $this->creationProgressBar->advance();
            }

            $this->creationProgressBar->finish();
        }
    }

    protected function createModuleFolder($modulename)
    {
        $this->filesystem->mkdir($this->getModuleDirectory($modulename));
    }

    protected function createMainModuleFile($moduleName, $moduleAuthor)
    {
        $controller_code = $this->twig->render($this->baseFolder . DIRECTORY_SEPARATOR . 'main.php.twig', [
            'module_name' => $moduleName,
            'module_author' => $moduleAuthor,
            'clean_module_name' => $this->findAndReplaceTool->sanitizeWords($moduleName),
        ]);

        $this->filesystem->dumpFile(
            $this->getModuleDirectory($moduleName) . DIRECTORY_SEPARATOR . $moduleName . '.php',
            $controller_code
        );
    }

    protected function createComposerJson($moduleName, $moduleAuthor, $namespace, $email = null, $license = null, $description = null, $website = null)
    {
        $composer_code = $this->twig->render($this->baseFolder . DIRECTORY_SEPARATOR . 'composer.json.twig', [
            'module_name' => $moduleName,
            'module_author' => $this->findAndReplaceTool->sanitizeWords($moduleAuthor),
            'email' => $email,
            'license' => $license,
            'description' => $description, /* TODO: Escape characters */
            'website' => $website,
            'test' => $this->testGeneration,
            'name_space_psr4' => str_replace('\\', '\\\\', $namespace),
        ]);

        $this->filesystem->dumpFile(
            $this->getModuleDirectory($moduleName) . DIRECTORY_SEPARATOR . 'composer.json',
            $composer_code
        );

        $jsonContent = file_get_contents($this->getModuleDirectory($moduleName) . DIRECTORY_SEPARATOR . 'composer.json');
        $data = json_decode($jsonContent);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->displayMessage(
                $this->translator->trans('Decode JSON error: %json_last_error_msg%', ['%json_last_error_msg' => json_last_error_msg()]),
                'error'
            );
            exit;
        }
        $prettyJsonContent = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        $this->filesystem->dumpFile(
            $this->getModuleDirectory($moduleName) . DIRECTORY_SEPARATOR . 'composer.json',
            $prettyJsonContent
        );
    }

    protected function createConfig($moduleName, $moduleAuthor, $nameSpace, $className)
    {
        $services_yml = $this->twig->render($this->baseAdminConfigFolder . DIRECTORY_SEPARATOR . 'services.yml.twig', [
            'module_name' => $this->findAndReplaceTool->sanitizeWords($moduleName),
            'module_author' => $this->findAndReplaceTool->sanitizeWords($moduleAuthor),
            'class_name' => $className,
            'name_space' => $nameSpace,
        ]);
        $module_config_path =
            $this->getModuleDirectory($moduleName) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'admin';
        $this->filesystem->mkdir($this->baseAdminConfigFolder);
        $this->filesystem->dumpFile(
            $module_config_path . DIRECTORY_SEPARATOR . 'services.yml',
            $services_yml
        );
    }

    protected function createRoute($moduleName, $moduleAuthor, $nameSpace)
    {
        $module_route_path = $this->getModuleDirectory($moduleName) . DIRECTORY_SEPARATOR . 'config';
        if ($this->filesystem->exists($module_route_path) === false) {
            $this->filesystem->mkdir($module_route_path);
        }

        $route_code = $this->twig->render($this->baseConfigFolder . DIRECTORY_SEPARATOR . 'routes.yml.twig', [
            'module_name' => $this->findAndReplaceTool->sanitizeWords($moduleName),
            'module_author' => $this->findAndReplaceTool->sanitizeWords($moduleAuthor),
            'name_space' => $nameSpace,
        ]);

        $this->filesystem->dumpFile($module_route_path . DIRECTORY_SEPARATOR . 'routes.yml', $route_code);
    }

    protected function createController($moduleName, $moduleAuthor, $nameSpace)
    {
        $controller_code =
            $this->twig->render($this->baseControllerFolder . DIRECTORY_SEPARATOR . 'configuration.php.twig', [
                'class_name' => 'Configuration',
                'module_name' => $moduleName,
                'name_space' => $nameSpace,
                'module_author' => $moduleAuthor,
                'clean_module_author' => $this->findAndReplaceTool->sanitizeWords($moduleAuthor),
            ]);

        $this->filesystem->dumpFile($this->getModuleDirectory($moduleName) . DIRECTORY_SEPARATOR . 'src' .
            DIRECTORY_SEPARATOR . 'Controller' . DIRECTORY_SEPARATOR . 'ConfigurationController.php', $controller_code);

        return 0;
    }

    public function createConfigurationType($moduleName, $nameSpace)
    {
        $configurationType = $this->twig->render($this->baseTypeFolder . DIRECTORY_SEPARATOR . 'configuration.form.type.php.twig', [
            'module_name' => $this->findAndReplaceTool->sanitizeWords($moduleName),
            'name_space' => $nameSpace,
            'class_name' => 'Configuration',
        ]);

        $this->filesystem->dumpFile(
            $this->getModuleDirectory($moduleName) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'Type' . DIRECTORY_SEPARATOR . 'ConfigurationFormType.php',
            $configurationType
        );

        return 0;
    }

    public function createConfigurationDataConfiguration($moduleName, $nameSpace)
    {
        $dataConfiguration = $this->twig->render($this->baseFormFolder . DIRECTORY_SEPARATOR . 'data.configuration.php.twig', [
            'module_name' => $this->findAndReplaceTool->sanitizeWords($moduleName),
            'name_space' => $nameSpace,
            'class_name' => 'Configuration',
        ]);

        $this->filesystem->dumpFile(
            $this->getModuleDirectory($moduleName) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'Form' . DIRECTORY_SEPARATOR . 'ConfigurationDataConfiguration.php',
            $dataConfiguration
        );

        return 0;
    }

    public function createConfigurationFormDataProvider($moduleName, $moduleNamespace)
    {
        $configurationFormDataProvider =
            $this->twig->render($this->baseFormFolder . DIRECTORY_SEPARATOR . 'form.data.provider.php.twig', [
                'name_space' => $moduleNamespace,
                'class_name' => 'Configuration',
            ]);

        $this->filesystem->dumpFile(
            $this->getModuleDirectory($moduleName) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'Form' . DIRECTORY_SEPARATOR . 'ConfigurationFormDataProvider.php',
            $configurationFormDataProvider
        );

        return 0;
    }

    protected function createControllerForm($modulename, $namespace)
    {
        $controller_code = $this->twig->render($this->baseControllerFolder . DIRECTORY_SEPARATOR . 'form.php.twig', [
            'class_name' => 'ConfigurationType',
            'module_name' => $modulename,
            'name_space' => $namespace,
        ]);

        $this->filesystem->dumpFile(
            $this->getModuleDirectory($modulename) . DIRECTORY_SEPARATOR . 'src' .
            DIRECTORY_SEPARATOR . 'Form' . DIRECTORY_SEPARATOR . 'Type' . DIRECTORY_SEPARATOR . 'ConfigurationType.php',
            $controller_code
        );

        return 0;
    }

    protected function createControllerTemplate($modulename)
    {
        $module_view_path =
            $this->getModuleDirectory($modulename) . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'templates';
        $this->filesystem->mkdir($module_view_path);

        $controllerView = $this->twig->render(
            $this->baseTemplatesFolder . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'form.html.twig',
            [
                'moduleName' => $modulename,
            ]
        );

        $this->filesystem->dumpFile(
            $module_view_path . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'form.html.twig',
            $controllerView
        );
        /*$this->filesystem->copy(
            $this->baseTemplatesFolder . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'form.html.twig',
            $module_view_path . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'form.html.twig'
        );*/

        return 0;
    }

    protected function createFrontController($module_name, $front_controller_name)
    {
        $front_controller_folder =
            $this->getModuleDirectory($this->moduleName) . DIRECTORY_SEPARATOR . 'controllers' . DIRECTORY_SEPARATOR . 'front';

        $this->filesystem->mkdir($front_controller_folder);

        $model_front_file_name = $this->baseControllerFolder . DIRECTORY_SEPARATOR . 'front_controller.php.twig';
        $front_controller_code = $this->twig->render($model_front_file_name, [
            'module_name' => $module_name,
            'front_controller_name' => $front_controller_name,
        ]);

        $front_filename = $front_controller_folder . DIRECTORY_SEPARATOR . $front_controller_name . '.php';
        $this->filesystem->dumpFile($front_filename, $front_controller_code);

        return 0;
    }

    protected function createFrontControllerJavascript($module_name, $front_controller_name)
    {
        $js_folder = $this->getModuleDirectory($this->moduleName) . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'js';
        $this->filesystem->mkdir($js_folder);

        $js_front_controller_code =
            $this->twig->render($this->baseViewFolder . DIRECTORY_SEPARATOR . 'js' . DIRECTORY_SEPARATOR .
                'front_controller.js.twig', [
                'module_name' => $module_name,
                'front_controller_name' => $front_controller_name,
            ]);
        $this->filesystem->dumpFile(
            $js_folder . DIRECTORY_SEPARATOR . $front_controller_name . '.js',
            $js_front_controller_code
        );

        return 0;
    }

    protected function createTest($modulename)
    {
        $module_dir = $this->getModuleDirectory($modulename);
        $test_dir = $module_dir . DIRECTORY_SEPARATOR . 'test';
        $this->filesystem->mkdir($test_dir);
        $this->filesystem->copy(
            $this->baseTestFolder . DIRECTORY_SEPARATOR . 'bootstrap.php.twig',
            $test_dir . DIRECTORY_SEPARATOR . 'bootstrap.php'
        );
        $this->filesystem->copy(
            $this->baseTestFolder . DIRECTORY_SEPARATOR . 'phpunit.xml.twig',
            $module_dir . DIRECTORY_SEPARATOR . 'phpunit.xml'
        );

        return 0;
    }

    /**
     * @param string|string[] $modulename
     *
     * @return string
     */
    protected function getModuleDirectory($modulename): string
    {
        return _PS_MODULE_DIR_ . $modulename;
    }

    /**
     * @param mixed $moduleAuthor
     *
     * @return ModuleGenerate
     */
    public function setModuleAuthor($moduleAuthor)
    {
        $this->moduleAuthor = $moduleAuthor;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getModuleAuthor()
    {
        return $this->moduleAuthor;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    public function getWebsite()
    {
        return $this->website;
    }

    public function setWebsite($website)
    {
        $this->website = $website;

        return $this;
    }

    public function getLicense()
    {
        return $this->license;
    }

    public function setLicense($license)
    {
        $this->license = $license;

        return $this;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    public function setBaseAdminConfigFolder(string $baseAdminConfigFolder): ModuleGenerate
    {
        $this->baseAdminConfigFolder = $baseAdminConfigFolder;

        return $this;
    }

    public function getBaseAdminConfigFolder(): string
    {
        return $this->baseAdminConfigFolder;
    }

    public function setBaseFormFolder(string $baseFormFolder): ModuleGenerate
    {
        $this->baseFormFolder = $baseFormFolder;

        return $this;
    }

    public function setBaseTypeFolder(string $baseTypeFolder): ModuleGenerate
    {
        $this->baseTypeFolder = $baseTypeFolder;

        return $this;
    }

    protected function displayMessage($message, $type = 'info')
    {
        $this->output->writeln(
            $this->formatter->formatBlock($message, $type, true)
        );
    }
}
