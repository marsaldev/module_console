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

namespace MCM\Console\Controllers;

use Controller;

/**
 * Controller used in Console environment.
 */
class ConsoleController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->controller_type = 'console';
    }

    /**
     * {@inheritdoc}
     */
    public function checkAccess()
    {
        // TODO: Implement checkAccess() method.
    }

    /**
     * {@inheritdoc}
     */
    public function viewAccess()
    {
        // TODO: Implement viewAccess() method.
    }

    /**
     * {@inheritdoc}
     */
    public function postProcess()
    {
        // TODO: Implement postProcess() method.
    }

    /**
     * {@inheritdoc}
     */
    public function display()
    {
        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function setMedia()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function initHeader()
    {
        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function initContent()
    {
        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function initCursedPage()
    {
        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function initFooter()
    {
        return '';
    }

    /**
     * {@inheritdoc}
     */
    protected function redirect()
    {
        return '';
    }

    /**
     * {@inheritdoc}
     */
    protected function buildContainer()
    {
        // @todo: Should we return the back office container here ?
        return $this->get('container');
    }
}
