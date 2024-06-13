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

namespace MCM\Console\Commands\About;

use Exception;
use MCM\Console\Command;
use GuzzleHttp\Client;
use RuntimeException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class AboutVersion extends Command
{
    public const GITHUB_RELEASES_YAML_URL = 'https://api.github.com/repos/marsaldev/module_console_manager/releases/latest';

    /**
     * @var mixed
     *            PrestaShop\PrestaShop\Core\Module\ModuleRepositoryInterface or PrestaShop\PrestaShop\Core\Addon\Module\ModuleRepositoryInterface
     *            (same interface but moved for v8 version)
     */
    private $moduleRepository;

    protected function configure(): void
    {
        $this->setName('mcm:about:version')
            ->setDescription('Prints the version of Module Console Manager aka mcm');
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        try {
            parent::initialize($input, $output);

            // get module's information from the Core, not the Adapter, not the legacy, this is the correct way.
            $this->moduleRepository = $this->getContainer()->get('prestashop.core.admin.module.repository');

            // For v8, `ModuleRepositoryInterface` as been moved
            $isPsBeforeV8 = version_compare(_PS_VERSION_, '8.0.0', '<');
            $isModuleRepositoryExpectedType = $isPsBeforeV8 ?
                /* @phpstan-ignore-next-line */
                ($this->moduleRepository instanceof \PrestaShop\PrestaShop\Core\Addon\Module\ModuleRepositoryInterface) : ($this->moduleRepository instanceof \PrestaShop\PrestaShop\Core\Module\ModuleRepositoryInterface);

            if (!$isModuleRepositoryExpectedType) {
                throw new RuntimeException('Failed to get the ModuleRepository prestashop.core.admin.module.repository');
            }
        } catch (Exception $exception) {
            $this->io->isVerbose()
                ? $this->getApplication()->renderException($exception, $output)
                : $output->write("<error> >>> Error on initialization : {$exception->getMessage()}</error> .");

            exit(1);
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $fopModule = $this->moduleRepository->getModule('fop_console');
        $properties = [
            'registered version' => $fopModule->get('version'),
            'disk version' => $fopModule->get('version_available'),
            'last release' => $this->getLastReleaseVersion(),
        ];

        $this->io->table(array_keys($properties), [$properties]);

        return 0;
    }

    private function getLastReleaseVersion(): string
    {
        try {
            // file_get_contents() fails with a 403 error.
            $HttpClient = new Client();
            $response = $HttpClient->get(self::GITHUB_RELEASES_YAML_URL);
            if ($response->getReasonPhrase() !== 'OK') {
                throw new \Exception('Not a 200 Response.');
            }

            return json_decode($response->getBody()->getContents())->tag_name;
        } catch (\Exception $exception) {
            if ($this->io->isVerbose()) {
                if (isset($response)) {
                    dump($response->getReasonPhrase());
                    dump($response->getHeaders());
                    dump($response->getBody()->getContents());
                }
                $this->io->error($exception->getMessage());
            }

            return 'Failed to retrieve version on GitHub (use -v for details)';
        }
    }
}