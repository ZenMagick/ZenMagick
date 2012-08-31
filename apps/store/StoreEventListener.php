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
namespace ZenMagick\apps\store;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

use ZenMagick\Base\Runtime;
use ZenMagick\Base\ZMObject;
use ZenMagick\Http\Session\FlashBag;
use ZenMagick\Base\Events\Event;
use ZenMagick\apps\store\widgets\StatusCheck;

/**
 * Shared store event listener.
 *
 * <p>This is the ZenMagick store bootstrapper.</p>
 *
 * @author DerManoMann
 */
class StoreEventListener extends ZMObject {
    /**
     * {@inheritDoc}
     */
    public function onRequestReady($event) {
        $request = $event->get('request');

        $cPath = array();
        if (null !== ($path = $request->query->get('cPath'))) {
            $path = explode('_', $path);
            foreach ($path as $categoryId) {
                $categoryId = (int)$categoryId;
                if (!in_array($categoryId, $cPath)) {
                    $cPath[] = $categoryId;
                }
            }
        }
        $request->attributes->set('categoryIds', $cPath);
        $currentCategoryId = end($cPath);
        $request->attributes->set('categoryId', (int)$currentCategoryId);


    }

    /**
     * Set up block manager.
     */
    public function onContainerReady($event) {

        $settingsService = $this->container->get('settingsService');

        $request = $event->get('request');

        if (Runtime::isContextMatch('storefront')) {
            $theme = $this->container->get('themeService')->getActiveTheme();
            $args = array('theme' => $theme, 'themeId' => $theme->getId());
            $event->getDispatcher()->dispatch('theme_loaded', new Event($this, $args));

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
        $mappings['banners'][] = 'ZenMagick\apps\store\widgets\BannerBlockWidget';

        // individual banner groups as per current convention
        $defaultBannerGroupNames = array(
            'banners.header1', 'banners.header2', 'banners.header3',
            'banners.footer1', 'banners.footer2', 'banners.footer3',
            'banners.box1', 'banners.box2',
            'banners.all'
        );
        foreach ($defaultBannerGroupNames as $blockGroupName) {
            // the banner group name is configured as setting..
            $bannerGroup = $settingsService->get($blockGroupName);
            $mappings[$blockGroupName] = array('ZenMagick\apps\store\widgets\BannerBlockWidget#group='.$bannerGroup);
        }

        // shopping cart options
        $mappings['shoppingCart.options'] = array();
        $mappings['shoppingCart.options'][] = 'ZenMagick\apps\store\widgets\PayPalECButtonBlockWidget';
        $mappings['mainMenu'] = array();
        $mappings['mainMenu'][] = 'ref::browserIDLogin';

        $this->container->get('blockManager')->setMappings($mappings);

        // status messages
        if (Runtime::isContextMatch('storefront')) {

            // check DFM
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
            foreach ($this->container->get('containerTagService')->findTaggedServiceIds('apps.store.admin.dashboard.widget.statusCheck') as $id => $args) {
                $statusCheck = $this->container->get($id);
                $messages = array_merge($messages, $statusCheck->getStatusMessages());
            }
            $statusMap = array(
                StatusCheck::STATUS_DEFAULT => FlashBag::T_MESSAGE,
                StatusCheck::STATUS_INFO => FlashBag::T_MESSAGE,
                StatusCheck::STATUS_NOTICE => FlashBag::T_WARN,
                StatusCheck::STATUS_WARN => FlashBag::T_WARN,
            );
            $messageService = $request->getSession()->getFlashBag();
            foreach ($messages as $details) {
                $messageService->addMessage($details[1], $statusMap[$details[0]]);
            }
        }
    }

}
