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


/**
 * Group pricing.
 *
 * @author mano
 * @package org.zenmagick.service
 * @version $Id$
 */
class ZMGroupPricing extends ZMObject {

    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
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
        return ZMObject::singleton('GroupPricing');
    }


    /**
     * Get all price groups.
     *
     * @return array List of ZMPriceGroup objects.
     */
    function getPriceGroups() {
        $db = ZMRuntime::getDB();
        $sql = "select * from " . TABLE_GROUP_PRICING;

        $groups = array();
        $results = $db->Execute($sql);
        while (!$results->EOF) {
            $groups[] = $this->_newPriceGroup($results->fields);
            $results->MoveNext();
        }

        return $groups;
    }

    /**
     * Get a price group for the given id.
     *
     * @param int priceGroupId The id.
     * @return ZMPriceGroup The group or <code>null</code>.
     */
    function getPriceGroupForId($priceGroupId) {
        $db = ZMRuntime::getDB();
        $sql = "select *
                from " . TABLE_GROUP_PRICING . "
                where  group_id = :priceGroupId";
        $sql = $db->bindVars($sql, ":priceGroupId", $priceGroupId, "integer");

        $results = $db->Execute($sql);
        $priceGroup = null;
        if (0 < $results->RecordCount()) {
            $priceGroup = $this->_newPriceGroup($results->fields);
        }
        return $priceGroup;
    }

    /**
     * Create new price group instance.
     */
    function _newPriceGroup($fields) {
        $priceGroup = ZMLoader::make("PriceGroup");
        $priceGroup->id_ = $fields['group_id'];
        $priceGroup->name_ = $fields['group_name'];
        $priceGroup->discount_ = $fields['group_percent'];
        $priceGroup->dateAdded_ = $fields['date_added'];
        $priceGroup->lastModified_ = $fields['last_modified'];
        return $priceGroup;
    }

}

?>
