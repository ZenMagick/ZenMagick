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
namespace zenmagick\base;

use zenmagick\base\dependencyInjection\compiler\ConfigureContainerTagServicePass;
use zenmagick\base\dependencyInjection\compiler\ResolveMergeDefinitionsPass;
use zenmagick\base\dependencyInjection\compiler\PluginsPass;
use zenmagick\base\dependencyInjection\ZenmagickExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Zenmagick Bundle.
 */
class ZenmagickBundle extends Bundle {

    /**
     * {@inheritDoc}
     */
    public function build(ContainerBuilder $container) {
        parent::build($container);
        $container->registerExtension(new ZenmagickExtension());
        $container->addCompilerPass(new ConfigureContainerTagServicePass());
        $container->addCompilerPass(new ResolveMergeDefinitionsPass());
        $container->addCompilerPass(new PluginsPass());
    }

    public function boot() {
        $parameterBag = $this->container->getParameterBag();
        $settingsService = $this->container->get('settingsService');
        foreach ($parameterBag->all()  as $param => $value) {
            $settingsService->set($param, $value);
        }

        $kernel = $this->container->get('kernel');
        $settingsFiles = array();
        $settingsFiles[] = $kernel->getRootDir().'/apps/base/config/config.yaml';
        $settingsFiles[] = $kernel->getApplicationPath().'/config/config.yaml';
        // @todo do something better for non store apps
        $settingsFiles[] = $kernel->getRootDir().'/config/store-config.yaml';
        foreach ($settingsFiles as $config) {
            if (file_exists($config)) {
                $settingsService->load($config);
            }
        }

        \ZMRuntime::setDatabase('default', $settingsService->get('apps.store.database.default'));

        if ($this->container->has('configService')) {
            foreach ($this->container->get('configService')->loadAll() as $key => $value) {
                if (!defined($key)) {
                    define($key, $value);
                }
            }

            $defaults = $kernel->getRootDir().'/apps/store/config/defaults.php';
            if (file_exists($defaults)) {
                include $defaults;
            }
        }

        $globalFilename = realpath($kernel->getRootDir().'/global.yaml');
        if (file_exists($globalFilename)) {
            $contextConfigLoader = $this->container->get('contextConfigLoader');
            $contextConfigLoader->setConfig($globalFilename);
            $config = $contextConfigLoader->resolve();
            unset($config['container']); // @todo remove when contextConfigLoader no longer has it
            $contextConfigLoader->apply($config);
        }

        // @todo never do this
        Runtime::setContainer($this->container);
        $context = $kernel->getContext();
        // @todo switch to using tagged services for events.
        $settingsService = $this->container->get('settingsService');
        $listeners = $settingsService->get('zenmagick.base.events.listeners', array());
        $plugins = $this->container->get('pluginService')->getPluginsForContext($context, true);
        foreach ($plugins as $plugin) {
            // @todo the plugin list will continue disabled plugins on the requests that build the cache.
            if (!$plugin->isEnabled()) continue;
            $listeners[] = $plugin;
        }
        if ('storefront' == $context) {
            $listeners[] = sprintf('zenmagick\themes\%s\EventListener', $this->container->get('themeService')->getActiveThemeId());
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
}
