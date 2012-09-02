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
namespace ZenMagick\ZenCartBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

use ZenMagick\Base\Runtime;
use ZenMagick\ZenCartBundle\DependencyInjection\ZenCartExtension;

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
        $container->registerExtension(new ZenCartExtension());
    }


    /**
     * {@inheritDoc}
     */
    public function boot() {
        $this->initClassLoader();
    }

    /**
     * @todo Move this init back into ZenCartAutoLoader or an event
     * when we can lazy load the shopping cart class.
     */
    public function initClassLoader() {
        $isAdmin = Runtime::isContextMatch('admin');
        define('IS_ADMIN_FLAG', $isAdmin);
        $classLoader = new \Composer\AutoLoad\ClassLoader();
        $classLoader->register();


        $container = $this->container;
        $zcRoot = $this->container->getParameter('zencart.root_dir');
        $zc = $zcRoot.'/includes/classes/';
        $zca = $zcRoot.'/'.$this->container->getParameter('zencart.admin_dir').'/includes/classes/';
        $b  = __DIR__.'/bridge/includes/classes/';
        $ba  = __DIR__.'/bridge/admin/includes/classes/';
        $map = array(
            'base' => $b.'class.base.php',
            'shoppingCart' => $zc.'shopping_cart.php',
            'navigationHistory' => $zc.'navigation_history.php',
            'currencies' => ($isAdmin ? $zca : $zc).'currencies.php',
            'httpClient' => $zc.'http_client.php',
            'messageStack' => ($isAdmin ? $ba : $b).'message_stack.php',
            'notifier' => $zc.'class.notifier.php',
            'queryFactory' => $b.'db/mysql/query_factory.php',
            'queryFactoryResult' => $b.'db/mysql/query_factory.php',
            // ZenCart admin/storefront
            'breadcrumb' => $zc.'breadcrumb.php',
            'category_tree' => $zc.'category_tree.php',
            'language' => ($isAdmin ? $ba : $b).'language.php',
            'products' => $zca.'products.php',
            'sniffer' => $zc.'sniffer.php',
            'splitPageResults' => ($isAdmin ? $zca : $zc).'split_page_results.php', // admin overrides storefront
            'template_func' => $zc.'template_func.php',
            'PHPMailer' => $zc.'class.phpmailer.php',
            'SMTP' => $zc.'class.smtp.php',
            'payment' => $zc.'payment.php',
            'order_total' => $zc.'order_total.php',
            'shipping' => $zc.'shipping.php',
            'order' => ($isAdmin ? $zca : $zc).'order.php',
            // ZenCart Admin
            'box' => $zca.'box.php',
            'objectInfo' => $zca.'object_info.php',
            'tableBlock' => $zca.'table_block.php',
            'upload' => $zca.'upload.php',
        );

        $classLoader->addClassMap($map);

    }

    /**
     * {@inheritDoc}
     */
    public function getContainerExtension() {
        return new DependencyInjection\ZenCartExtension;
    }

}
