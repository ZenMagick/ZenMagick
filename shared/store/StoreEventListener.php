<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2012 zenmagick.org
 *
 * Portions Copyright (c) 2003 The zen-cart developers
 * Portions Copyright (c) 2003 osCommerce
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
namespace zenmagick\apps\store;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

use zenmagick\base\Runtime;
use zenmagick\base\ZMObject;
use zenmagick\http\messages\Messages;
use zenmagick\apps\store\widgets\StatusCheck;

/**
 * Shared store event listener.
 *
 * <p>This is the ZenMagick store bootstrapper.</p>
 *
 * @author DerManoMann
 */
class StoreEventListener extends ZMObject {

    /**
     * Get config loaded ASAP.
     */
    public function onInitConfigDone($event) {
        foreach ($this->container->get('configService')->loadAll() as $key => $value) {
            if (!defined($key)) {
                define($key, $value);
            }
        }

        $defaults = Runtime::getInstallationPath().'/shared/defaults.php';
        if (file_exists($defaults)) {
            include $defaults;
        }

        // load email container config once all settings/config is loaded
        $emailConfig = Runtime::getInstallationPath().'/config/store-email.xml';
        if (file_exists($emailConfig)) {
            $containerlLoader = new XmlFileLoader($this->container, new FileLocator(dirname($emailConfig)));
            $containerlLoader->load($emailConfig);
        }
    }

    /**
     * Set up block manager.
     */
    public function onContainerReady($event) {
        $request = $event->get('request');

        if (Runtime::isContextMatch('storefront')) {
            $templateManager = $this->container->get('templateManager');
            // TODO: do via admin and just load mapping from somewhere
            // sidebox blocks
            $mappings = array();
            if ($templateManager->isLeftColEnabled()) {
                $index = 1;
                $mappings['leftColumn'] = array();
                foreach ($templateManager->getLeftColBoxNames() as $boxName) {
                    // avoid duplicates by using $box as key
                    $mappings['leftColumn'][$boxName] = 'blockWidget#template=boxes/'.$boxName.'.php&sortOrder='.$index++;
                }
            }
            if ($templateManager->isRightColEnabled()) {
                $index = 1;
                $mappings['rightColumn'] = array();
                foreach ($templateManager->getRightColBoxNames() as $boxName) {
                    // avoid duplicates by using $box as key
                    $mappings['rightColumn'][$boxName] = 'blockWidget#template=boxes/'.$boxName.'.php&sortOrder='.$index++;
                }
            }
        }

        $this->container->get('bannerService')->runTasks();
        $this->container->get('salemakerService')->runTasks();
        $this->container->get('productFeaturedService')->runTasks();
        $this->container->get('productSpecialsService')->runTasks();

        // general banners block group - if used, the group needs to be passed into fetchBlockGroup()
        $mappings['banners'] = array();
        $mappings['banners'][] = 'zenmagick\apps\store\widgets\BannerBlockWidget';

        // individual banner groups as per current convention
        $defaultBannerGroupNames = array(
            'banners.header1', 'banners.header2', 'banners.header3',
            'banners.footer1', 'banners.footer2', 'banners.footer3',
            'banners.box1', 'banners.box2',
            'banners.all'
        );
        foreach ($defaultBannerGroupNames as $blockGroupName) {
            // the banner group name is configured as setting..
            $bannerGroup = Runtime::getSettings()->get($blockGroupName);
            $mappings[$blockGroupName] = array('zenmagick\apps\store\widgets\BannerBlockWidget#group='.$bannerGroup);
        }

        // shopping cart options
        $mappings['shoppingCart.options'] = array();
        $mappings['shoppingCart.options'][] = 'zenmagick\apps\store\widgets\PayPalECButtonBlockWidget';
        $mappings['mainMenu'] = array();
        $mappings['mainMenu'][] = 'ref::browserIDLogin';

        $this->container->get('blockManager')->setMappings($mappings);

        // status messages
        if (Runtime::isContextMatch('storefront')) {

            // check DFM
            $settingsService = $this->container->get('settingsService');
            $downForMaintenance = $settingsService->get('apps.store.downForMaintenance', false);
            $adminIps = $settingsService->get('apps.store.adminOverrideIPs');

            if ($downForMaintenance && !in_array($request->getClientIp(), $adminIps)) {
                // @todo this would be more appropriately placed in the controller or dispatcher,
                // but also needs to work if  don't get that far due to application errors and
                // should only work on storefront.
                header('HTTP/1.1 503 Service Unavailable');
                $dfmPages = $settingsService->get('apps.store.downForMaintenancePages');
                $dfmRoute = $settingsService->get('apps.store.downForMaintenanceRoute');
                $dfmPages[] = $dfmRoute;
                if (!in_array($request->getRequestId(), $dfmPages)) {
                    $url = $request->url($dfmRoute);
                    $request->redirect($url);
                    exit;
                }
            }

            $messages = array();
            foreach ($this->container->findTaggedServiceIds('apps.store.admin.dashboard.widget.statusCheck') as $id => $args) {
                $statusCheck = $this->container->get($id);
                $messages = array_merge($messages, $statusCheck->getStatusMessages());
            }
            $statusMap = array(
                StatusCheck::STATUS_DEFAULT => Messages::T_MESSAGE,
                StatusCheck::STATUS_INFO => Messages::T_MESSAGE,
                StatusCheck::STATUS_NOTICE => Messages::T_WARN,
                StatusCheck::STATUS_WARN => Messages::T_WARN,
            );
            $messageService = $this->container->get('messageService');
            foreach ($messages as $details) {
                $messageService->add($details[1], $statusMap[$details[0]]);
            }
        }
    }

}
