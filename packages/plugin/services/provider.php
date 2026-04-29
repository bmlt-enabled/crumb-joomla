<?php

/**
 * @package         Crumb
 * @copyright   (C) 2026 bmlt-enabled. All rights reserved.
 * @license         GNU General Public License version 2 or later
 */

\defined('_JEXEC') or die;

use BmltEnabled\Plugin\Content\Crumb\Extension\Crumb;
use Joomla\CMS\Extension\PluginInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Event\DispatcherInterface;

return new class () implements ServiceProviderInterface {
    public function register(Container $container): void
    {
        $container->set(
            PluginInterface::class,
            function (Container $container) {
                $dispatcher = $container->get(DispatcherInterface::class);
                $plugin     = PluginHelper::getPlugin('content', 'crumb');

                $extension = new Crumb($dispatcher, (array) $plugin);
                $extension->setApplication(Factory::getApplication());

                return $extension;
            }
        );
    }
};
