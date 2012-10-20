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

namespace ZenMagick\ZenMagickBundle\DependencyInjection;

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
        // @todo use bundle Resources for all all these files.
        $rootDir = dirname($container->getParameter('kernel.root_dir'));

        // define in Configuration class
        $config = array('plugins' => array('enabled' => false));
        foreach ($configs as $subConfig) {
            $config = array_merge($config, $subConfig);
        }
        $loader = $this->getLoader($container, new FileLocator($rootDir));
        $files = array();
        $files[] = 'lib/ZenMagick/ZenMagickBundle/Resources/config/base.xml';
        $files[] = 'lib/ZenMagick/ZenMagickBundle/Resources/config/http.xml';
        if ($config['plugins']['enabled']) {
            $files[] = 'lib/ZenMagick/ZenMagickBundle/Resources/config/plugins.xml';
        }

        $files[] = 'lib/ZenMagick/StoreBundle/config/container.xml';
        $files[] = 'apps/'.$context.'/config/container.xml';
        foreach ($files as $file) {
            $loader->load($file);
        }

        if ('admin' == $context) {
            $container->setParameter('zenmagick.http.sacs.mappingProviders', array('ZenMagick\apps\admin\Services\DBSacsPermissionProvider'));
        }

    }

    /**
     * {@inheritDoc}
     */
    public function getAlias() {
        return 'zenmagick';
    }
}

