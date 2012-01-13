<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2011 zenmagick.org
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
?>
<?php
namespace zenmagick\apps\store;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

use zenmagick\base\Runtime;
use zenmagick\base\Toolbox;
use zenmagick\base\ZMObject;

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

        $defaults = Runtime::getInstallationPath().'shared/defaults.php';
        if (file_exists($defaults)) {
            include $defaults;
        }

        // load email container config once all settings/config is loaded
        $emailConfig = Runtime::getInstallationPath().DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'store-email.xml';
        if (file_exists($emailConfig)) {
            $containerlLoader = new XmlFileLoader(Runtime::getContainer(), new FileLocator(dirname($emailConfig)));
            $containerlLoader->load($emailConfig);
        }

        // load some static files that we still need
        $statics = array(
            'storefront' => array('shared/store/bundles/ZenCartBundle/utils/zencart_overrides.php')
        );
        foreach ($statics as $context => $files) {
            if (Toolbox::isContextMatch($context)) {
                foreach ($files as $static) {
                    $file = Runtime::getInstallationPath().$static;
                    if (file_exists($file)) {
                        require_once $file;
                    }
                }
            }
        }
    }

    /**
     * Keep up support for local.php.
     */
    public function onBootstrapDone($event) {
        $local = Runtime::getInstallationPath().DIRECTORY_SEPARATOR.'local.php';
        if (file_exists($local)) {
            include $local;
        }
    }

    /**
     * Set up block manager.
     */
    public function onInitDone($event) {
        $request = $event->get('request');

        if (Toolbox::isContextMatch('storefront')) {
            $templateManager = $this->container->get('templateManager');
            // TODO: do via admin and just load mapping from somewhere
            // sidebox blocks
            $mappings = array();
            if ($templateManager->isLeftColEnabled()) {
                $index = 1;
                $mappings['leftColumn'] = array();
                foreach ($templateManager->getLeftColBoxNames() as $boxName) {
                    // avoid duplicates by using $box as key
                    $mappings['leftColumn'][$boxName] = 'blockWidget#template=boxes/'.$boxName.'&sortOrder='.$index++;
                }
            }
            if ($templateManager->isRightColEnabled()) {
                $index = 1;
                $mappings['rightColumn'] = array();
                foreach ($templateManager->getRightColBoxNames() as $boxName) {
                    // avoid duplicates by using $box as key
                    $mappings['rightColumn'][$boxName] = 'blockWidget#template=boxes/'.$boxName.'&sortOrder='.$index++;
                }
            }
        }

        // general banners block group - if used, the group needs to be passed into fetchBlockGroup()
        $mappings['banners'] = array();
        $mappings['banners'][] = 'ZMBannerBlockWidget';

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
            $mappings[$blockGroupName] = array('ZMBannerBlockWidget#group='.$bannerGroup);
        }

        // shopping cart options
        $mappings['shoppingCart.options'] = array();
        $mappings['shoppingCart.options'][] = 'ZMPayPalECButtonBlockWidget';
        $mappings['mainMenu'] = array();
        $mappings['mainMenu'][] = 'ref::browserIDLogin';

        $this->container->get('blockManager')->setMappings($mappings);
    }

}
