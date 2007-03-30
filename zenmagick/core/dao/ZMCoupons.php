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
 * Coupons.
 *
 * @author mano
 * @package net.radebatz.zenmagick.dao
 * @version $Id$
 */
class ZMCoupons extends ZMDao {

    /**
     * Default c'tor.
     */
    function ZMCoupons() {
        parent::__construct();

        $this->countries_ = null;
    }

    /**
     * Default c'tor.
     */
    function __construct() {
        $this->ZMCoupons();
    }

    /**
     * Default d'tor.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Coupon lookup for the given code.
     *
     * @param string code The coupons code.
     * @return ZMCoupon A <code>ZMCoupon</code> instance or <code>null</code>.
     */
    function getCouponForCode($code) {
    global $zm_runtime;

        $sql = "select * from " . TABLE_COUPONS . " c
                left join " . TABLE_COUPONS_DESCRIPTION . " cd
                on (c.coupon_id = cd.coupon_id
                and cd.language_id = '" . $zm_runtime->getLanguageId() . "')
                where c.coupon_code = :code";
        $sql = $this->db_->bindVars($sql, ':code', $code, 'string');
        $results = $this->db_->Execute($sql);

        $coupon = null;
        if (0 < $results->RecordCount()) {
            $coupon = $this->_newCoupon($results->fields);
        }

        return $coupon;
    }

    // create coupon from map
    function _newCoupon($fields) {
        $coupon =& $this->create("Coupon", $fields['coupon_id'], $fields['coupon_code'], $fields['coupon_type']);
        $coupon->amount_ = $fields['coupon_amount'];
        $coupon->name_ = $fields['coupon_name'];
        $coupon->description_ = $fields['coupon_description'];
        $coupon->minimumOrder_ = $fields['coupon_minimum_order'];
        $coupon->startDate_ = $fields['coupon_start_date'];
        $coupon->expiryDate_ = $fields['coupon_expire_date'];
        $coupon->usesPerCoupon_ = $fields['uses_per_coupon'];
        $coupon->usesPerUser_ = $fields['uses_per_user'];
        return $coupon;
    }

    // get coupon restrictions
    function _getRestrictionsForId($id) {
        $sql = "select * from " . TABLE_COUPON_RESTRICT . "
                where coupon_id = :id";
        $sql = $this->db_->bindVars($sql, ':id', $id, 'string');
        $results = $this->db_->Execute($sql);

        $categories = array();
        $products = array();
        while (!$results->EOF) {
            if (0 != $results->fields['category_id']) {
                $restriction =& $this->create("CategoryCouponRestriction", $results->fields['coupon_restrict'] == 'N', $results->fields['category_id']);
                array_push($categories, $restriction);
            } else {
                $restriction =& $this->create("ProductCouponRestriction", $results->fields['coupon_restrict'] == 'N', $results->fields['product_id']);
                array_push($products, $restriction);
            }
            $results->MoveNext();
        }
        return $this->create("CouponRestrictions", $categories, $products);
    }

}

?>
