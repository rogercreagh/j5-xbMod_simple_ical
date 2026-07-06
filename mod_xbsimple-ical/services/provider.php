<?php
/*******
 * @package xbSimple-ical
 * @filesource mod_xbsimple-ical/services/provider.php
 * @version 0.2.0.0 8th May 2026
 * @since v0.1.0.0 29th April 2026
 * @copyright Copyright (c) Roger Creagh-Osborne, 2026
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @desc based on SimpleIcalBlock by  A.H.C. Waasdorp (c) 2022-2026 https://www.waasdorpsoekhan.nl
 ******/


defined('_JEXEC') or die;

use Joomla\CMS\Extension\Service\Provider\HelperFactory;
use Joomla\CMS\Extension\Service\Provider\Module;
use Joomla\CMS\Extension\Service\Provider\ModuleDispatcherFactory;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;

/**
 * The popular articles module service provider.
 *
 * @since  4.3.0
 */
return new class () implements ServiceProviderInterface {
    /**
     * Registers the service provider with a DI container.
     *
     * @param   Container  $container  The DI container.
     *
     * @return  void
     *
     * @since   4.3.0
     */
    public function register(Container $container)
    {
        $container->registerServiceProvider(new ModuleDispatcherFactory('\\Crosborne\\Module\\Xbsimpleical'));
        $container->registerServiceProvider(new HelperFactory('\\Crosborne\\Module\\Xbsimpleical\\Site\\Helper'));

        $container->registerServiceProvider(new Module());
    }
};
