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
    global $zm_request, $zm_banners, $zm_manufacturers, $zm_languages;

        $action = $zm_request->getParameter('action');
        $goto = $zm_request->getParameter('goto');

        switch ($action) {
        case 'banner':
            $banner = $zm_banners->getBannerForId($goto);
            if (null != $banner) {
                $zm_banners->updateBannerClickCount($goto);
                return $this->findView('success', array('url' => $banner->getUrl()));
            }
            break;

        case 'url':
            if (null != $goto) {
                return $this->findView('success', array('url' => $goto));
            }
            break;

        case 'manufacturer':
            $manufacturerId = $zm_request->getManufacturerId();
            if (0 < $manufacturerId) {
                $manufacturer = $zm_manufacturers->getManufacturerForId($manufacturerId);

                if (null == $manufacturer || null == $manufacturer->getURL()) {
                    // try default language if different from session language
                    if (zm_setting('defaultLanguageCode') != $zm_runtime->getLanguageCode()) {
                        $defaultLanguage = $zm_languages->getLanguageForCode(zm_setting('defaultLanguageCode'));
                        $manufacturer = $zm_manufacturers->getManufacturerForId($manufacturerId, $defaultLanguage->getId());
                    }
                }

                if (null != $manufacturer && null != $manufacturer->getURL()) {
                    $zm_manufacturers->updateManufacturerClickCount($manufacturerId);
                    return $this->findView('success', array('url' => $manufacturer->getUrl()));
                }
                
            }
            break;
        }
            
        return $this->findView('error');
    }

}

?>
