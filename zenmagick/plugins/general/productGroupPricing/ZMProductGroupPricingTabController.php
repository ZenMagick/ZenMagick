<?php
/*
 * ZenMagick - Smart e-commerce
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
 */
class ZMProductGroupPricingTabController extends ZMCatalogContentController {

    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct('product_group_pricing_tab', _zm('Group Pricing'), self::ACTIVE_PRODUCT);
    }


    /**
     * {@inheritDoc}
     */
    public function getViewData($request) {
        $priceGroups = ZMGroupPricing::instance()->getPriceGroups();
        $groupId = $request->getParameter('groupId', $priceGroups[0]->getId());
        $productGroupPricings = ZMProductGroupPricings::instance()->getProductGroupPricings($request->getProductId(), $groupId, false);
        $productGroupPricing = ZMLoader::make("ProductGroupPricing");
        // TODO: should not need to check for delete - viewData should not override findView(.., data) data
        if (null != ($groupPricingId = $request->getParameter('groupPricingId')) && 0 < $groupPricingId && null == $request->getParameter('delete')) {
            $productGroupPricing = ZMProductGroupPricings::instance()->getProductGroupPricingForId($groupPricingId);
        }
        return array(
            'groupId' => $groupId,
            'priceGroups' => $priceGroups,
            'productGroupPricing' => $productGroupPricing,
            'productGroupPricings' => $productGroupPricings
        );
    }

    /**
     * {@inheritDoc}
     */
    public function processGet($request) {
        //TODO: this should be POST!!
        if (ZMLangUtils::asBoolean($request->getParameter('delete'))) {
            $productGroupPricing = ZMLoader::make("ProductGroupPricing");
            $productGroupPricing->populate($request);
            // delete
            ZMProductGroupPricings::instance()->updateProductGroupPricing($productGroupPricing);
        }
        return $this->findView(null, array('productGroupPricing' => ZMLoader::make("ProductGroupPricing")));
    }

    /**
     * {@inheritDoc}
     */
    public function processPost($request) {
        $productGroupPricing = ZMLoader::make("ProductGroupPricing");
        $productGroupPricing->populate($request);
        if (0 == $productGroupPricing->getId()) {
            // create
            $productGroupPricing = ZMProductGroupPricings::instance()->createProductGroupPricing($productGroupPricing);
        } else {
            // update
            $productGroupPricing = ZMProductGroupPricings::instance()->updateProductGroupPricing($productGroupPricing);
        }

        return $this->findView('catalog-redirect');
    }

}
