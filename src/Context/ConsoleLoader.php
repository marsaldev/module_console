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

namespace MCM\Console\Context;

use Configuration;
use Currency;
use Employee;
use MCM\Console\Controllers\ConsoleController;
use PrestaShop\PrestaShop\Adapter\LegacyContext;
use PrestaShop\PrestaShop\Adapter\Shop\Context as ShopContext;
use Symfony\Component\Console\Exception\LogicException;
use Symfony\Component\Console\Input\InputInterface;

/**
 * PrestaShop Context in Console Application
 */
final class ConsoleLoader
{
    private $legacyContext;
    private $shopContext;
    private $rootDir;

    public function __construct(LegacyContext $legacyContext, ShopContext $shopContext, $rootDir)
    {
        $this->legacyContext = $legacyContext;
        $this->shopContext = $shopContext;
        $this->rootDir = $rootDir;
        require_once $rootDir . '/../config/config.inc.php';
    }

    public function loadConsoleContext(InputInterface $input)
    {
        if (!defined('_PS_ADMIN_DIR_')) {
            define('_PS_ADMIN_DIR_', $this->rootDir);
        }
        $employeeId = $input->getOption('employee');
        $shopId = $input->getOption('id_shop');
        $shopGroupId = $input->getOption('id_shop_group');
        if ($shopId && $shopGroupId) {
            throw new LogicException('Do not specify an ID shop and an ID group shop at the same time.');
        }
        $this->legacyContext->getContext()->controller = new ConsoleController();
        if (!$this->legacyContext->getContext()->employee) {
            $this->legacyContext->getContext()->employee = new Employee((int) $employeeId);
        }
        $shop = $this->legacyContext->getContext()->shop;
        $shop::setContext(1);
        if ($shopId === null) {
            $shopId = 1;
        }
        $this->shopContext->setShopContext($shopId);
        $this->legacyContext->getContext()->shop = $shop;
        if ($shopGroupId !== null) {
            $this->shopContext->setShopGroupContext($shopGroupId);
        }
        $this->legacyContext->getContext()->currency = new Currency((int) Configuration::get('PS_CURRENCY_DEFAULT') ?: null);
    }
}
