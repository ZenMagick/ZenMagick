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
class ZenCartExtension extends Extension {

    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container) {

        $config = array();
        foreach ($configs as $subConfig) {
            $config = array_merge($config, $subConfig);
        }

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));

        $rootDir = $container->getParameter('kernel.root_dir');

        if (isset($config['root_dir']) && !empty($config['root_dir'])) {
            $container->setParameter('zencart.root_dir', $config['root_dir']);
        }

        // @todo we can autodetect!
        if (!$container->hasParameter('zencart.root_dir')) {
            $container->setParameter('zencart.root_dir', realpath(dirname(dirname($rootDir))));
        }


        // Set path to zencart admin directory.
        $adminDir = null;
        if (isset($config['admin_dir']) && !empty($config['admin_dir'])) {
            $adminDir = $config['admin_dir'];
        }

        if (null == $adminDir) {
            $adminDir = $this->guessZcAdminDir($container->getParameter('zencart.root_dir'));
        }

        $container->setParameter('zencart.admin_dir', $adminDir);

        $loader->load('services.xml');
    }

    /**
     * Get ZenCart admin directory.
     *
     * @param string zcRootDir path to zencart root
     * @return string
     */
    protected function guessZcAdminDir($zcRootDir) {
        $finder = Finder::create()->files()->in($zcRootDir)->depth('== 1')
            ->name('featured.php')->name('specials.php');

        if (2 != count($finder)) return;
        foreach($finder as $file) {
            $adminDir = dirname($file->getRealpath());
        }
        return $adminDir;
    }

    /**
     * {@inheritDoc}
     */
    public function getAlias() {
        return 'zencart';
    }
}

