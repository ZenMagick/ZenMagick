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

use zenmagick\base\Beans;
use zenmagick\base\Runtime;
use zenmagick\base\utils\Executor;
use zenmagick\apps\store\bundles\ZenCartBundle\utils\EmailEventHandler;

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

    /**
     * Handle things that require a request.
     */
    public function onRequestReady($event) {
        $request = $event->get('request');
        // @todo doesn't really belong here. it just needs to be this early
        $request->getSession()->restorePersistedServices();

        $event->getDispatcher()->addListener('generate_email', array(Beans::getBean('zenmagick\apps\store\bundles\ZenCartBundle\utils\EmailEventHandler'), 'onGenerateEmail'));

        if (Runtime::isContextMatch('storefront')) {
            $autoLoader = $this->container->get('zenCartAutoLoader');
            $autoLoader->initCommon();
            $autoLoader->setGlobalValue('currencies', new \currencies);

        }
    }

    /**
     * Boot ZenCart template and language
     */
    public function onDispatchStart($event) {
        // @todo all this code should go somewhere else
        if (defined('DIR_WS_TEMPLATE') || !Runtime::isContextMatch('storefront')) return;
        $autoLoader = $this->container->get('zenCartAutoLoader');
        $themeId = $this->container->get('themeService')->getActiveThemeId();
        $autoLoader->setGlobalValue('template_dir', $themeId);
        define('DIR_WS_TEMPLATE', DIR_WS_TEMPLATES.$themeId.'/');
        define('DIR_WS_TEMPLATE_IMAGES', DIR_WS_TEMPLATE.'images/');
        define('DIR_WS_TEMPLATE_ICONS', DIR_WS_TEMPLATE_IMAGES.'icons/');

        // required for the payment,checkout,shipping modules
        $autoLoader->setErrorLevel();
        $autoLoader->includeFiles('includes/classes/db/mysql/define_queries.php');
        $autoLoader->includeFiles('includes/languages/%template_dir%/%language%.php');
        $autoLoader->includeFiles('includes/languages/%language%.php');
        $autoLoader->includeFiles(array(
            'includes/languages/%language%/extra_definitions/%template_dir%/*.php',
            'includes/languages/%language%/extra_definitions/*.php')
        );
        $autoLoader->restoreErrorLevel();
    }

    public function onViewStart($event) {
        $settingsService = $this->container->get('settingsService');
        if (Runtime::isContextMatch('admin')) {
            $settingsService->add('apps.store.admin.menus', 'apps/store/bundles/ZenCartBundle/Resources/config/admin/menu.yaml');
            $settingsService->add('zenmagick.http.routing.addnRouteFiles', __DIR__.'/Resources/config/admin/routing.xml');
        }
    }
}
