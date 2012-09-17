<?php
/*
 * ZenMagick - Another PHP framework.
 * Copyright (C) 2006-2012 zenmagick.org
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or (at
 * your option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street - Fifth Floor, Boston, MA  02110-1301, USA.
 */
namespace ZenMagick\ZenMagickBundle;

use ZenMagick\ZenMagickBundle\DependencyInjection\Compiler\ConfigureContainerTagServicePass;
use ZenMagick\ZenMagickBundle\DependencyInjection\Compiler\ResolveMergeDefinitionsPass;
use ZenMagick\ZenMagickBundle\DependencyInjection\Compiler\PluginsPass;
use ZenMagick\ZenMagickBundle\DependencyInjection\ZenMagickExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * ZenMagick Bundle.
 */
class ZenMagickBundle extends Bundle {

    /**
     * {@inheritDoc}
     */
    public function build(ContainerBuilder $container) {
        parent::build($container);
        $container->registerExtension(new ZenMagickExtension());
        $container->addCompilerPass(new ConfigureContainerTagServicePass());
        $container->addCompilerPass(new ResolveMergeDefinitionsPass());
        $container->addCompilerPass(new PluginsPass());
    }

    public function boot() {
        $parameterBag = $this->container->getParameterBag();
        $settingsService = $this->container->get('settingsService');

        $settingsService->set('zencart.root_dir', $parameterBag->get('zencart.root_dir'));
        $settingsService->set('zencart.admin_dir', $parameterBag->get('zencart.admin_dir'));

        $rootDir = $parameterBag->get('zenmagick.root_dir');
        $settingsFiles = array();
        $settingsFiles[] = $rootDir.'/lib/ZenMagick/StoreBundle/config/config.yaml';
        $settingsFiles[] = $parameterBag->get('kernel.context_dir').'/config/config.yaml';
        // @todo do something better for non store apps
        $settingsFiles[] = $rootDir.'/config/store-config.yaml';
        foreach ($settingsFiles as $config) {
            if (file_exists($config)) {
                $settingsService->load($config);
            }
        }

        // @todo never do this
        \Zenmagick\Base\Runtime::setContainer($this->container);

        // @todo don't just exit if no plugins
        if (!$this->container->has('pluginService')) return;

        if ($this->container->has('configService')) {
            foreach ($this->container->get('configService')->loadAll() as $key => $value) {
                if (!defined($key)) {
                    define($key, $value);
                }
            }

            $defaults = $rootDir.'/lib/ZenMagick/StoreBundle/config/defaults.php';
            if (file_exists($defaults)) {
                include $defaults;
            }
        }

        $context = $parameterBag->get('kernel.context');
        // @todo switch to using tagged services for events.
        $settingsService = $this->container->get('settingsService');
        $listeners = $settingsService->get('zenmagick.base.events.listeners', array());

        if ($this->container->has('pluginService')) {
            $plugins = $this->container->get('pluginService')->getPluginsForContext($context, true);
            foreach ($plugins as $plugin) {
                // @todo the plugin list will continue disabled plugins on the requests that build the cache.
                if (!$plugin->isEnabled()) continue;
                $listeners[] = $plugin;
            }
        }
        if ('storefront' == $context) {
            $listeners[] = sprintf('ZenMagick\themes\%s\EventListener', $this->container->get('themeService')->getActiveThemeId());
        }

        // @todo switch to using tagged services for events.
        $dispatcher = $this->container->get('event_dispatcher');
        foreach ($listeners as $eventListener) {
            if (is_string($eventListener)) {
                if (!class_exists($eventListener)) continue;
                if (null != ($eventListener = new $eventListener)) {
                    $eventListener->setContainer($this->container);
                }
            }

            if (is_object($eventListener)) {
                $events = array();
                foreach (get_class_methods($eventListener) as $method) {
                    if (0 === strpos($method, 'on')) {
                        $eventName = Container::underscore(substr($method, 2));
                        $dispatcher->addListener($eventName, array($eventListener, $method));
                    }

                }
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getContainerExtension() {
        return new DependencyInjection\ZenMagickExtension();
    }
}
