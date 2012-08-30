<?php
/*
 * ZenMagick - Smart e-commerce
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
namespace zenmagick\apps\store\bundles\ZenCartBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

use zenmagick\base\Runtime;
use zenmagick\apps\store\bundles\ZenCartBundle\DependencyInjection\ZencartExtension;

/**
 * Zencart support bundle.
 *
 * @author DerManoMann
 */
class ZenCartBundle extends Bundle {

    /**
     * {@inheritDoc}
     */
    public function build(ContainerBuilder $container) {
        parent::build($container);
        $container->registerExtension(new ZencartExtension());
    }

    /**
     * {@inheritDoc}
     */
    public function boot() {
        define('IS_ADMIN_FLAG', Runtime::isContextMatch('admin'));
        $classLoader = new \Composer\AutoLoad\ClassLoader();
        $classLoader->register();
        $map = array(
            'base' => __DIR__.'/bridge/includes/classes/class.base.php',
            'shoppingCart' => $this->container->getParameter('zencart.root_dir').'/includes/classes/shopping_cart.php',
            'navigationHistory' => $this->container->getParameter('zencart.root_dir').'/includes/classes/navigation_history.php'
        );
        $classLoader->addClassMap($map);

    }
}
