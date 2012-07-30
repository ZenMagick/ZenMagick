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
        $eventDispatcher = Runtime::getEventDispatcher();
        $eventDispatcher->listen($this);
        $eventDispatcher->addListener('generate_email', array(Beans::getBean('zenmagick\apps\store\bundles\ZenCartBundle\utils\EmailEventHandler'), 'onGenerateEmail'));

    }

    /**
     * Handle things that require a request.
     */
    public function onRequestReady($event) {
        $request = $event->get('request');

        if (Runtime::isContextMatch('storefront')) {
            $autoLoader = $this->container->get('zenCartAutoLoader');
            $autoLoader->initCommon();
            $autoLoader->setGlobalValue('currencies', new \currencies);

            $session = $request->getSession();

            if (null == $session->getValue('cart')) {
                $session->setValue('cart', new \shoppingCart);
            }
            if (null == $session->getValue('navigation')) {
                $session->setValue('navigation', new \navigationHistory);
            }
        }
        $settingsService = $this->container->get('settingsService');
        if (Runtime::isContextMatch('admin')) {
            $settingsService->add('apps.store.admin.menus', 'apps/store/bundles/ZenCartBundle/Resources/config/admin/menu.yaml');
            $settingsService->add('zenmagick.http.routing.addnRouteFiles', __DIR__.'/Resources/config/admin/routing.xml');
        }

    }

    /**
     * Boot ZenCart template and language
     */
    public function onDispatchStart($event) {
        // @todo all this code should go somewhere else
        if (defined('DIR_WS_TEMPLATE') || !Runtime::isContextMatch('storefront')) return;
        $autoLoader = $this->container->get('zenCartAutoLoader');
        $session = $event->get('request')->getSession();
        $language = $session->getLanguage();
        $themeId = $this->container->get('themeService')->getActiveThemeId($language->getId());
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

    /**
     * Switch over to ZenCartStorefrontController if required.
     *
     * @todo this is the wrong place because error templates show
     * the "base" template.
     */
    public function onControllerProcessStart($event) {
        if (!Runtime::isContextMatch('storefront')) return;

        $request = $event->get('request');
        $language = $request->getSession()->getLanguage();
        $needsZC = $this->container->get('themeService')->getActiveTheme($language->getId())->getMeta('zencart');
        // @todo <johnny> we want to use the route instead right?
        // || in_array($request->getRequestId(), (array)$this->container->get('settingsService')->get('apps.store.request.enableZCRequestHandling));
        if ($needsZC && null != ($dispatcher = $request->getDispatcher())) {
            $settingsService = $this->container->get('settingsService');
            $settingsService->set('zenmagick.http.view.defaultLayout', null);
            $executor = new Executor(array($this->container->get('zenmagick\apps\store\bundles\ZenCartBundle\controller\ZencartStorefrontController'), 'process'), array($request));
            $dispatcher->setControllerExecutor($executor);
        }
     }
}
