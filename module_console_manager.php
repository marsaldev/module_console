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
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to infos@friendsofpresta.org so we can send you a copy immediately.
 *
 * @author    Marco Salvatore <hi@marcosalvatore.dev>
 * @copyleft since 2024 Marco Salvatore
 * @license   https://opensource.org/licenses/AFL-3.0  Academic Free License ("AFL") v. 3.0
 *
 */

$autoloadPath = __DIR__ . '/vendor/autoload.php';
if (file_exists($autoloadPath)) {
    require_once $autoloadPath;
}

class ModuleConsoleManager extends Module
{
    public function __construct()
    {
        $this->name = 'module_console_manager';
        $this->version = '1.0.0';
        $this->author = 'Marco Salvatore (marsaldev)';

        parent::__construct();

        $this->displayName = 'Module Console Manager';
        $this->description = $this->l('Set of command lines to perform operations for PrestaShop modules.');
        $this->ps_versions_compliancy = [
            'min' => '1.7.5.0',
            'max' => _PS_VERSION_,
        ];
    }

    public function install()
    {
        if (PHP_VERSION_ID < 70200) {
            $this->_errors[] = $this->l('module_console_manager require at least php version 7.2.');

            return false;
        }

        return parent::install();
    }
}
