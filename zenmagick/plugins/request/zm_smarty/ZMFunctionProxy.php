<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2009 ZenMagick
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
 * Smarty funciton proxy.
 *
 * @package org.zenmagick.plugins.zm_smarty
 * @author DerManoMann
 * @version $Id$
 */
class ZMFunctionProxy {
    private $services;


    /**
     * Create new instance.
     */
    function __construct() {
        $this->services = array(
            // can't do static from smarty
            'ZMRequest' => new ZMRequest(),
            'ZMSettings' => new ZMSettings(),
            'ZMRuntime' => new ZMRuntime(),

            // managed by ZMRequest
            'ZMShoppingCart' => ZMRequest::instance()->getShoppingCart(),

            'ZMLoader' => ZMLoader::instance(),
            'ZMTemplateManager' => ZMTemplateManager::instance(),
            'ZMProducts' => ZMProducts::instance(),
            'ZMTaxRates' => ZMTaxRates::instance(),
            'ZMReviews' => ZMReviews::instance(),
            'ZMEZPages' => ZMEZPages::instance(),
            'ZMCoupons' => ZMCoupons::instance(),
            'ZMBanners' => ZMBanners::instance(),
            'ZMOrders' => ZMOrders::instance(),
            'ZMEvents' => ZMEvents::instance(),
            'ZMAddresses' => ZMAddresses::instance(),
            'ZMMessages' => ZMMessages::instance(),
            'ZMValidator' => ZMValidator::instance(),
            'ZMCategories' => ZMCategories::instance(),
            'ZMManufacturers' => ZMManufacturers::instance(),
            'ZMCrumbtrail' => ZMCrumbtrail::instance(),
            'ZMMetaTags' => ZMMetaTags::instance(),
            'ZMCurrencies' => ZMCurrencies::instance(),
            'ZMLanguages' => ZMLanguages::instance(),
            'ZMCountries' => ZMCountries::instance(),
            'ZMAccounts' => ZMAccounts::instance(),
            'ZMUrlMapper' => ZMUrlMapper::instance(),
            'ZMSacsManager' => ZMSacsManager::instance()
        );
    }

    /**
     * Allow access to services.
     *
     * @param string name The name.
     */
    public function __get($name) {
        if (isset($this->services[$name])) {
            return $this->services[$name];
        }
        return null;
    }

    /**
     * Acts as proxy for all ZenMagick functions to be used by templates.
     *
     * @param string method The function to call.
     * @param array args The function arguments.
     */
    public function __call($method, $args) {
        if (function_exists('zm_'.$method)) {
            $method = 'zm_'.$method;
        }
        return call_user_func_array($method, $args);
    }

}

?>
