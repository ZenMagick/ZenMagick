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

// in case admin switches aren't added properly, assume default settings:
if (!defined('MAX_DISPLAY_XSELL')) define('MAX_DISPLAY_XSELL',6);
if (!defined('MIN_DISPLAY_XSELL')) define('MIN_DISPLAY_XSELL',1);
if (!defined('XSELL_DISPLAY_PRICE')) define('XSELL_DISPLAY_PRICE','false');
if (!defined('SHOW_PRODUCT_INFO_COLUMNS_XSELL_PRODUCTS')) define('SHOW_PRODUCT_INFO_COLUMNS_XSELL_PRODUCTS',3);


/**
 * Plugin for crossell support.
 *
 * @package org.zenmagick.plugins.zm_crossell
 * @author DerManoMann
 * @version $Id: zm_crossell.php 337 2007-09-05 23:47:54Z DerManoMann $
 */
class zm_crossell extends ZMPlugin {

    /**
     * Default c'tor.
     */
    function zm_crossell() {
        parent::__construct('ZenMagick Crossell', 'Adds Crossell support for ZenMagick', '${plugin.version}');
        $this->setLoaderSupport('ALL');
    }

    /**
     * Default c'tor.
     */
    function __construct() {
        $this->zm_crossell();
    }

    /**
     * Default d'tor.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Init this plugin.
     */
    function init() {
    global $zm_categories;

        parent::init();

        $zm_categories = $this->create("Categories");
    }


    /**
     * Get x-sell products for the given productId.
     *
     * <p>This will return no products, if the number of products is less than the 
     * configured minimum. It is possible to ignore the min setting by setting
     * <code>ignoreMinLimit</code> to <code>true</code>.</p>
     *
     * <p>Will return an empty list if the plugin is disabled.</p>
     *
     * @param int productId The product id.
     * @param boolean ignoreMinLimit If set to true, the minimum limit will be ignored; default is false
     * @return array List of <code>ZMProduct</code> objects.
     */
    function getXSellForProductId($productId, $ignoreMinLimit=false) {
    global $zm_products;

        if (!$this->isEnabled()) {
            return array();
        }

        $db = $this->getDB();
        $sql = "select distinct xp.xsell_id from " . TABLE_PRODUCTS_XSELL . " xp
                where xp.products_id = :productId
                order by xp.sort_order asc limit :limit";
        $sql = $db->bindVars($sql, ":productId", $productId, "integer");
        $sql = $db->bindVars($sql, ":limit", MAX_DISPLAY_XSELL, "integer");

        $productIds = array();
        $results = $db->Execute($sql);
        while (!$results->EOF) {
            array_push($productIds, $results->fields['xsell_id']);
            $results->MoveNext();
        }

        if (!$ignoreMinLimit && count(productIds) < MIN_DISPLAY_XSELL) {
            return array();
        }

        return $zm_products->getProductsForIds($productIds);
    }

}

?>
