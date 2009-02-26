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
 *
 * $Id$
 */
?>
<?php

    /**
     * Group pricing admin page.
     *
     * @package org.zenmagick.plugins.zm_group_pricing
     * @return ZMPluginPage A plugin page or <code>null</code>.
     */
    function zm_group_pricing_admin() {
    global $zm_nav_params;

        $zm_nav_params .= '&fkt=zm_group_pricing_admin';
        $toolbox = ZMToolbox::instance();

        $priceGroups = ZMGroupPricing::instance()->getPriceGroups();

        // request handling
        $groupPricingService = ZMLoader::make("ProductGroupPricingService");
        if ('GET' == ZMRequest::getMethod()) {
            $productId = ZMRequest::getProductId();
            $groupId = ZMRequest::getParameter('groupId', 0);
            $productGroupPricing = $groupPricingService->getProductGroupPricing($productId, $groupId, false);
            if (null !== $productGroupPricing) {
                // ugh: populate request for initial display
                ZMRequest::setParameter('groupPricingId', $productGroupPricing->getId());
                ZMRequest::setParameter('discount', $productGroupPricing->getDiscount());
                ZMRequest::setParameter('type', $productGroupPricing->getType());
                ZMRequest::setParameter('regularPriceOnly', $productGroupPricing->isRegularPriceOnly());
                ZMRequest::setParameter('startDate', $productGroupPricing->getStartDate());
                ZMRequest::setParameter('endDate', $productGroupPricing->getEndDate());
            }
        } else if ('POST' == ZMRequest::getMethod()) {
            $productGroupPricing = ZMLoader::make("ProductGroupPricing");
            $productGroupPricing->populate();
            if (0 == $productGroupPricing->getId()) {
                // create
                $productGroupPricing = $groupPricingService->createProductGroupPricing($productGroupPricing);
            } else {
                // update
                $productGroupPricing = $groupPricingService->updateProductGroupPricing($productGroupPricing);
            }
            $groupId = ZMRequest::getParameter('groupId', 0);
            ZMRequest::redirect($toolbox->net->url('', $zm_nav_params.'&groupId='.$groupId, false, false));
        }


        // execute view
        $plugin = ZMPlugins::getPluginForId('zm_group_pricing');
        $template = file_get_contents($plugin->getPluginDir().'/views/group_pricing_admin.php');
        eval('?>'.$template);

        return new ZMPluginPage('zm_group_pricing_admin', zm_l10n_get('Group Pricing'));
    }

?>
