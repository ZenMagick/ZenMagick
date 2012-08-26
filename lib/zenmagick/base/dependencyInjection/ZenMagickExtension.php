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

namespace zenmagick\base\dependencyInjection;

use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\LoaderResolver;
use Symfony\Component\Config\Loader\DelegatingLoader;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

/**
 */
class ZenMagickExtension extends Extension {

    /**
     * @todo seems like this functionality should already be
     * available?
     */
    protected function getLoader(ContainerBuilder $container, FileLocator $locator) {
        $resolver = new LoaderResolver(array(
            new XmlFileLoader($container, $locator),
            new PhpFileLoader($container, $locator),
        ));
        return new DelegatingLoader($resolver);
    }

    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container) {
        $context = $container->getParameter('kernel.context');
        $rootDir = $container->getParameter('kernel.root_dir');
        $loader = $this->getLoader($container, new FileLocator($rootDir));
        $files = array();
        $files[] = 'lib/zenmagick/base/container.xml';
        $files[] = 'lib/zenmagick/http/container.xml';
        $files[] = 'apps/'.$context.'/config/container.xml';
        if (file_exists($rootDir.'/config/container.xml')) {
            $files[] = 'config/container.xml';
        }
        foreach ($files as $file) {
            $loader->load($file);
        }

        if ('admin' == $context) {
            $container->setParameter('zenmagick.http.sacs.mappingProviders', array('zenmagick\apps\admin\services\DBSacsPermissionProvider'));
        }

        // @todo still in the wrong place!
        $container->get('pluginService')->getPluginsForContext($context);
        if ('storefront' == $context) {
            $container->get('themeService')->initThemes();
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getAlias() {
        return 'zenmagick';
    }
}

