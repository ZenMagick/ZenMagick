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
 * @version $Id$
 */
class zm_crossell extends ZMPlugin {

    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct('Crossell', 'Adds Crossell support for ZenMagick', '${plugin.version}');
        $this->setLoaderPolicy(ZMPlugin::LP_ALL);
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }

    /**
     * Init this plugin.
     */
    function init() {
        parent::init();

        ZMDbTableMapper::instance()->setMappingForTable('products_xsell', 
            array(
                'xSellId' => 'column=ID;type=integer;key=true;auto=true',
                'productId' => 'column=products_id;type=integer',
                'xsProductId' => 'column=xsell_id;type=integer',
                'sortOrder' => 'column=sort_order;type=integer',
              )
        );
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
    public function getXSellForProductId($productId, $ignoreMinLimit=false) {
        $productIds = array();
        if (!$this->isEnabled()) {
            return $productIds;
        }

        $sql = "SELECT DISTINCT xp.xsell_id from " . TABLE_PRODUCTS_XSELL . " xp
                WHERE xp.products_id = :productId
                ORDER BY xp.sort_order ASC limit ".(int)MAX_DISPLAY_XSELL;

        foreach (ZMRuntime::getDatabase()->query($sql, array('productId' => $productId), TABLE_PRODUCTS_XSELL, ZMDatabase::MODEL_RAW) as $result) {
            $productIds[] = $result['xsell_id'];
        }

        if (!$ignoreMinLimit && count(productIds) < MIN_DISPLAY_XSELL) {
            return array();
        }

        return ZMProducts::instance()->getProductsForIds($productIds);
    }

}

?>
