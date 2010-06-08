<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2010 zenmagick.org
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
 * General access class for shipping provider.
 *
 * @author DerManoMann
 * @package zenmagick.store.shared.services.checkout
 */
class ZMShippingProviders extends ZMObject {
    private $providers_;


    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
        $this->providers_ = array();
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }

    /**
     * Get instance.
     */
    public static function instance() {
        return ZMObject::singleton('ShippingProviders');
    }


    /**
     * Get a shipping provider for the given id.
     *
     * @param string shippingProviderId The shipping provider id.
     * @return ZMShippingProvider A shipping provider or <code>null</code>
     */
    public function getShippingProviderForId($shippingProviderId) {
        $configured = false;
        if (!isset($this->providers_[$configured])) {
            // load
            $providers = $this->getShippingProviders($configured);
        } else {
            $providers = $this->providers_[$configured];
        }
        foreach ($providers as $provider) {
            if ($provider->getId() == $shippingProviderId) {
                return $provider;
            }
        }

        return null;
    }

    /**
     * Get a list of shipping providers.
     *
     * @param boolean configured If <code>true</code>, return only configured provider; default is <code>true</code>.
     * @return array List of <code>ZMShippingProvider</code> instances.
     */
    public function getShippingProviders($configured=true) {
        if (isset($this->providers_[$configured])) {
            return $this->providers_[$configured];
        }

        // required by some
        ZMTools::resolveZCClass('http_client');

        $this->providers_[$configured] = array();

        $moduleInfos = array();
        if ($configured) {
            if (defined('MODULE_SHIPPING_INSTALLED') && !ZMLangUtils::isEmpty(MODULE_SHIPPING_INSTALLED)) {
                $files = explode(';', MODULE_SHIPPING_INSTALLED);
                foreach ($files as $file) {
                    $clazz = substr($file, 0, strrpos($file, '.'));
                    $moduleInfos[$file] = array('class' => $clazz, 'file' => $file);
                }
            }
        } else {
            $module_directory = DIR_FS_CATALOG . DIR_WS_MODULES . 'shipping/';
            if ($dir = @dir($module_directory)) {
                while ($file = $dir->read()) {
                    if (!is_dir($module_directory . $file) && substr($file, strrpos($file, '.')) == '.php') {
                        $clazz = substr($file, 0, strrpos($file, '.'));
                        $moduleInfos[$file] = array('class' => $clazz, 'file' => $file);
                    }
                }
                $dir->close();
            }
        }
        sort($moduleInfos);

        //TODO:(1): bad, bad hack to make admin's zen_get_shipping_enabled() work for pages other than admin/modules.php
        global $PHP_SELF;
        $phpSelf = $PHP_SELF;
        $PHP_SELF = FILENAME_MODULES;

        // TODO: create fake environment
        global $template, $shipping_weight;
        if (!isset($template)) {
            ZMTools::resolveZCClass('template_func');
            $template = new template_func();
        }

        foreach ($moduleInfos as $moduleInfo) {
            $lang_file = DIR_FS_CATALOG . zen_get_file_directory(DIR_WS_LANGUAGES . $_SESSION['language'] . '/modules/shipping/', $moduleInfo['file'], 'false');
            if (@file_exists($lang_file)) {
                include_once($lang_file);
            }
            include_once(DIR_FS_CATALOG . DIR_WS_MODULES . 'shipping/' . $moduleInfo['file']);
            if (class_exists($moduleInfo['class'])) {
                // create instance
                $module = new $moduleInfo['class']();
                // either all or enabled (installed+enabled as per config option) - (is this different from $module->enabled?)
                if (!$configured || (0 < $module->check() && $module->enabled)) {
                    $this->providers_[$configured][] = ZMLoader::make("ShippingProviderWrapper", $module);
                }
            }
        }

        //TODO:(2): revert
        $PHP_SELF = $phpSelf;

        return $this->providers_[$configured];
    }

    /**
     * Get a list of all shipping providers for the given address.
     *
     * @param ZMShoppingCart shoppingCart The shopping cart.
     * @param ZMAddress address The address.
     * @return array List of <code>ZMShippingProvider</code> instances.
     */
    public function getShippingProvidersForAddress($shoppingCart, $address) {
        $available = array();
        foreach ($this->getShippingProviders() as $provider) {
            // check address
            $methods = $provider->getShippingMethods($shoppingCart, $address);
            if (0 < count($methods)) {
                $available[] = $provider;
            }
        }

        return $available;
    }

}
