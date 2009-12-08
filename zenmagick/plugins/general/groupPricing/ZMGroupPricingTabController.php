<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2009 zenmagick.org
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
 * @package org.zenmagick.plugins.groupPricing
 * @version $Id$
 */
class ZMGroupPricingTabController extends ZMPluginAdminController {

    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct('group_pricing_tab', zm_l10n_get('Group Pricing'), 'groupPricing');
    }


    /**
     * {@inheritDoc}
     */
    public function processGet($request) {
        $priceGroups = ZMGroupPricing::instance()->getPriceGroups();
        $groupId = $request->getParameter('groupId', $priceGroups[0]->getId());

        // need to do this to for using PluginAdminView rather than SimplePluginFormView
        return $this->findView(null, array('groupId' => $groupId, 'priceGroups' => $priceGroups));
    }

    /**
     * {@inheritDoc}
     */
    public function processPost($request) {
        $groupPricingService = ZMLoader::make("ProductGroupPricingService");

        $productGroupPricing = ZMLoader::make("ProductGroupPricing");
        $productGroupPricing->populate();
        if (0 == $productGroupPricing->getId()) {
            // create
            $productGroupPricing = $groupPricingService->createProductGroupPricing($productGroupPricing);
        } else {
            // update
            $productGroupPricing = $groupPricingService->updateProductGroupPricing($productGroupPricing);
        }
        return $this->getCatalogManagerRedirectView($request);
    }

}

?>
