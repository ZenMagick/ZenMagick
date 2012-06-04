<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2012 zenmagick.org
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

use zenmagick\base\Runtime;
use zenmagick\base\ZMObject;

/**
 * Group pricing.
 *
 * @author DerManoMann
 * @package zenmagick.store.shared.services.catalog
 */
class ZMGroupPricing extends ZMObject {

    /**
     * Get instance.
     */
    public static function instance() {
        return Runtime::getContainer()->get('groupPricingService');
    }


    /**
     * Get all price groups.
     *
     * @return array List of ZMPriceGroup objects.
     */
    function getPriceGroups() {
        $sql = "SELECT *
                FROM %table.group_pricing%";
        return ZMRuntime::getDatabase()->fetchAll($sql, array(), 'group_pricing', 'ZMPriceGroup');
    }

    /**
     * Get a price group for the given id.
     *
     * @param int priceGroupId The id.
     * @return ZMPriceGroup The group or <code>null</code>.
     */
    function getPriceGroupForId($priceGroupId) {
        $sql = "SELECT *
                FROM %table.group_pricing%
                WHERE  group_id = :id";
        return ZMRuntime::getDatabase()->querySingle($sql, array('id' => $priceGroupId), 'group_pricing', 'ZMPriceGroup');
    }

}
