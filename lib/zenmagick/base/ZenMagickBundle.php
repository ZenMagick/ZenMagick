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

use Symfony\Component\DependencyInjection\ContainerBuilder;
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
        $container->addScope(new Scope('request'));

        $container->addCompilerPass(new ConfigureContainerTagServicePass());
        $container->addCompilerPass(new ResolveMergeDefinitionsPass());
    }

    public function boot() {
        $parameterBag = $this->container->getParameterBag();
        foreach ($parameterBag->all()  as $param => $value) {
            $this->container->get('settingsService')->set($param, $value);
        }

        $globalFilename = realpath($parameterBag->get('kernel.root_dir').'/global.yaml');
        if (file_exists($globalFilename)) {
            $contextConfigLoader = $this->container->get('contextConfigLoader');
            $contextConfigLoader->setConfig($globalFilename);
            $config = $contextConfigLoader->resolve();
            unset($config['container']); // @todo remove when contextConfigLoader no longer has it
            $contextConfigLoader->apply($config);
        }

        $context = $parameterBag->get('kernel.context');
        // @todo switch to using tagged services for events.
        $settingsService = $this->container->get('settingsService');
        $listeners = $settingsService->get('zenmagick.base.events.listeners', array());
        $plugins = $this->container->get('pluginService')->getPluginsForContext($context);
        $listeners = array_merge($listeners, $plugins);

        if ('storefront' == $context) {
            $listeners[] = sprintf('zenmagick\themes\%s\EventListener', $this->container->get('themeService')->getActiveThemeId());
        }

        // @todo switch to using tagged services for events.
        foreach ($listeners as $eventListener) {
            if (is_string($eventListener)) {
                if (!class_exists($eventListener)) continue;
                if (null != ($eventListener = new $eventListener)) {
                    $eventListener->setContainer($this->container);
                }
            }
            if (is_object($eventListener)) {
                $this->container->get('event_dispatcher')->listen($eventListener);
            }
        }
    }
}
