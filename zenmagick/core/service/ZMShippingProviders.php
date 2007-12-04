<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006,2007 ZenMagick
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
 * Access class for shipping provider.
 *
 * @author mano
 * @package org.zenmagick.service
 * @version $Id$
 */
class ZMShippingProviders extends ZMService {
    var $provider_;


    /**
     * Default c'tor.
     */
    function __construct() {
        parent::__construct();
        $this->provider_ = null;
    }

    /**
     * Default c'tor.
     */
    function ZMShippingProviders() {
        $this->__construct();
    }

    /**
     * Default d'tor.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Get a list of all shipping provider.
     *
     * @param boolean configured If <code>true</code>, return only configured provider: default is <code>true</code>.
     * @return array List of <code>ZMShippingProvider</code> instances.
     */
    function getShippingProvider($configured=true) {
        if (null !== $this->provider_) {
            return $this->provider_;
        }

        $this->provider_ = array();

        $moduleInfos = array();
        if ($configured) {
            if (defined('MODULE_SHIPPING_INSTALLED') && !zm_is_empty(MODULE_SHIPPING_INSTALLED)) {
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

        foreach ($moduleInfos as $moduleInfo) {
            $lang_file = zen_get_file_directory(DIR_WS_LANGUAGES . $_SESSION['language'] . '/modules/shipping/', $moduleInfo['file'], 'false');
            if (@file_exists($lang_file)) {
                include_once($lang_file);
            }
            include_once(DIR_WS_MODULES . 'shipping/' . $moduleInfo['file']);
            if (class_exists($moduleInfo['class'])) {
                // create instance
                $module = new $moduleInfo['class']();
                if (!$configured || 0 < $module->check()) {
                    $this->provider_[] = $this->create("ShippingProviderWrapper", $module);
                }
            }
        }

        return $this->provider_;
    }

}

?>
