<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2008 ZenMagick
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


/**
 * Redirect controller.
 *
 * @author DerManoMann
 * @package org.zenmagick.rp.uip.controller
 * @version $Id$
 */
class ZMRedirectController extends ZMController {

    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
    }

    /**
     * Create new instance.
     */
    function ZMRedirectController() {
        $this->__construct();
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Process a HTTP GET request.
     * 
     * @return ZMView A <code>ZMView</code> that handles presentation or <code>null</code>
     * if the controller generates the contents itself.
     */
    function processGet() {
        $action = ZMRequest::getParameter('action');
        $goto = ZMRequest::getParameter('goto');

        switch ($action) {
        case 'banner':
            $banner = ZMBanners::instance()->getBannerForId($goto);
            if (null != $banner) {
                ZMBanners::instance()->updateBannerClickCount($goto);
                return $this->findView('success', array('url' => $banner->getUrl()));
            }
            break;

        case 'url':
            if (null != $goto) {
                return $this->findView('success', array('url' => $goto));
            }
            break;

        case 'manufacturer':
            $manufacturerId = ZMRequest::getManufacturerId();
            if (0 < $manufacturerId) {
                $manufacturer = ZMManufacturers::instance()->getManufacturerForId($manufacturerId);

                if (null == $manufacturer || null == $manufacturer->getURL()) {
                    // try default language if different from session language
                    if (ZMSettings::get('defaultLanguageCode') != ZMRequest::getSession()->getLanguageCode()) {
                        $defaultLanguage = ZMLanguages::instance()->getLanguageForCode(ZMSettings::get('defaultLanguageCode'));
                        $manufacturer = ZMManufacturers::instance()->getManufacturerForId($manufacturerId, $defaultLanguage->getId());
                    }
                }

                if (null != $manufacturer && null != $manufacturer->getURL()) {
                    ZMManufacturers::instance()->updateManufacturerClickCount($manufacturerId);
                    return $this->findView('success', array('url' => $manufacturer->getUrl()));
                }
                
            }
            break;
        }
            
        return $this->findView('error');
    }

}

?>
