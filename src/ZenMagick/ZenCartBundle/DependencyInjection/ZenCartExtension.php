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

namespace ZenMagick\ZenCartBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 */
class ZenCartExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        if (null === $config['root_dir']) {
            $rootDir = $container->getParameter('kernel.root_dir');
            $config['root_dir'] = dirname($rootDir).'/web';
        }

        $container->setParameter('zencart.root_dir', $config['root_dir']);

        if (null === $config['admin_dir']) {
            $config['admin_dir'] = $this->guessZcAdminDir($config['root_dir']);
        }

        $container->setParameter('zencart.admin_dir', $config['admin_dir']);

        $admin = $config['admin'];

        $container->setParameter('zencart.admin.hide_layout', $admin['hide_layout']);

        // @todo revaluate
        $nativeAdmin = isset($config['admin']['native']) && $config['admin']['native'];
        $container->setParameter('zencart.admin.native', $nativeAdmin);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));

        $loader->load('services.xml');
    }

    /**
     * Get ZenCart admin directory.
     *
     * @param string zcRootDir path to zencart root
     * @return string
     */
    private function guessZcAdminDir($zcRootDir)
    {
        $finder = Finder::create()->files()->in($zcRootDir)->depth('== 1')
            ->name('featured.php')->name('specials.php');

        if (2 != count($finder)) return;
        foreach ($finder as $file) {
            $adminDir = dirname($file->getRealpath());
        }

        return $adminDir;
    }

    /**
     * {@inheritDoc}
     */
    public function getAlias()
    {
        return 'zencart';
    }
}
