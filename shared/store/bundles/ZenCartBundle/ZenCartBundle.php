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

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

use zenmagick\base\Beans;
use zenmagick\base\Runtime;
use zenmagick\base\dependencyInjection\loader\YamlLoader;
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
        $settingsService = Runtime::getSettings();
        if (null == $settingsService->get('apps.store.zencart.path')) { // @todo or default to vendors/zencart?
            $settingsService->set('apps.store.zencart.path', dirname(Runtime::getInstallationPath()));
        }

        $eventDispatcher = Runtime::getEventDispatcher();
        $eventDispatcher->listen($this);
        $eventDispatcher->addListener('generate_email', array(Beans::getBean('zenmagick\apps\store\bundles\ZenCartBundle\utils\EmailEventHandler'), 'onGenerateEmail'));

    }

    /**
     * Prepare db config
     */
    public function onInitConfigDone($event) {
        $yaml = array('services' => array(
            // @todo restore this once it works again.
            //'zenCartThemeStatusMapBuilder' => array('parent' => 'merge:themeStatusMapBuilder', 'class' => 'zenmagick\apps\store\bundles\ZenCartBundle\mock\ZenCartThemeStatusMapBuilder'),
            'zenCartAutoLoader' => array('class' => 'zenmagick\apps\store\bundles\ZenCartBundle\utils\ZenCartAutoLoader'),
        ));

        $yamlLoader = new YamlLoader($this->container, new FileLocator(dirname(__FILE__)));
        $yamlLoader->load($yaml);

        $settingsService = $this->container->get('settingsService');
        if (Runtime::isContextMatch('admin')) {
            $settingsService->add('apps.store.admin.menus', 'shared/store/bundles/ZenCartBundle/Resources/config/admin/menu.yaml');
            $settingsService->add('zenmagick.http.routing.addnRouteFiles', __DIR__.'/Resources/config/admin/routing.xml');
        }
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

            if (!$request->isXmlHttpRequest()) {
                $session->getValue('navigation')->add_current_page();
            }


        }
    }

    /**
     * Things to do after the auto loader is finished, but before going back into index.php
     */
    public function onAutoloadDone($event) {
        if (!Runtime::isContextMatch('storefront')) return;
        $request = $event->get('request');

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
        $autoLoader->setGlobalValue('language_page_directory', 'includes/languages/'.$request->getSelectedLanguage()->getDirectory().'/');

        // skip more zc request handling
        global $code_page_directory;
        if (!$this->needsZC($request)) {
            $code_page_directory = 'zenmagick';
        } else {
            $code_page_directory = 'includes/modules/pages/'.$request->getRequestId();
        }
    }

    /**
     * Simple function to check if we need zen-cart request processing.
     *
     * @param /MRequest request The current request.
     * @return boolean <code>true</code> if zen-cart should handle the request.
     */
    private function needsZC($request) {
        if ($this->container->get('themeService')->getActiveTheme()->getMeta('zencart')) {
            return true;
        }

        $requestId = $request->getRequestId();
        $requestIds = Runtime::getSettings()->get('apps.store.request.enableZCRequestHandling', array());
        // not supported by ZenMagick (yet)
        $requestIds = array_merge($requestIds, array('checkout_confirmation', 'checkout_process'));

        $needs = false;
        if (in_array($requestId, $requestIds)) {
            Runtime::getLogging()->debug('enable zencart request processing for requestId='.$requestId);
            return true;
        }
        return $needs;
    }
}
