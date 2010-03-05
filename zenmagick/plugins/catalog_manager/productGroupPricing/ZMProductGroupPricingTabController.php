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
 * Admin controller.
 *
 * @author DerManoMann
 * @package org.zenmagick.plugins.productGroupPricing
 * @version $Id$
 */
class ZMProductGroupPricingTabController extends ZMPluginAdminController {

    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct('product_group_pricing_tab', zm_l10n_get('Group Pricing'), 'productGroupPricing');
    }


    /**
     * Get shared page data.
     *
     * @param ZMRequest request The current request.
     * @return array Some data map.
     */
    protected function getCommonViewData($request) {
        $priceGroups = ZMGroupPricing::instance()->getPriceGroups();
        $groupId = $request->getParameter('groupId', $priceGroups[0]->getId());
        $productGroupPricings = ZMProductGroupPricings::instance()->getProductGroupPricing($request->getProductId(), $groupId, false);
        return array('groupId' => $groupId, 'priceGroups' => $priceGroups, 'productGroupPricings' => $productGroupPricings);
    }

    /**
     * {@inheritDoc}
     */
    public function processGet($request) {
        // need to do this to for using PluginAdminView rather than SimplePluginFormView
        return $this->findView(null, $this->getCommonViewData($request));
    }

    /**
     * {@inheritDoc}
     */
    public function processPost($request) {
        $productGroupPricing = ZMLoader::make("ProductGroupPricing");
        $productGroupPricing->populate();
        if (0 == $productGroupPricing->getId()) {
            // create
            $productGroupPricing = ZMProductGroupPricings::instance()->createProductGroupPricing($productGroupPricing);
        } else {
            // update
            $productGroupPricing = ZMProductGroupPricings::instance()->updateProductGroupPricing($productGroupPricing);
        }

        // need to do this to for using PluginAdminView rather than SimplePluginFormView
        return $this->findView(null, $this->getCommonViewData($request));
    }

}
