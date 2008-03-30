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
 * Coupons.
 *
 * @author mano
 * @package org.zenmagick.service
 * @version $Id$
 */
class ZMCoupons extends ZMObject {

    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
        $this->countries_ = null;
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
        return ZMObject::singleton('Coupons');
    }


    /**
     * Coupon lookup for the given code.
     *
     * @param string code The coupons code.
     * @param int languageId The languageId; default is <code>null</code> for session language.
     * @return ZMCoupon A <code>ZMCoupon</code> instance or <code>null</code>.
     */
    function getCouponForCode($code, $languageId=null) {
        if (null === $languageId) {
            $session = ZMRequest::getSession();
            $languageId = $session->getLanguageId();
        }

        $db = ZMRuntime::getDB();
        $sql = "select c.coupon_id, c.coupon_code, c.coupon_type, c.coupon_amount, c.coupon_minimum_order, c.coupon_start_date,
                c.coupon_expire_date, c.uses_per_coupon, c.uses_per_user,
                cd.coupon_name, cd.coupon_description
                from " . TABLE_COUPONS . " c
                left join " . TABLE_COUPONS_DESCRIPTION . " cd
                on (c.coupon_id = cd.coupon_id
                and cd.language_id = :languageId)
                where c.coupon_code = :code";
        $sql = $db->bindVars($sql, ':code', $code, 'string');
        $sql = $db->bindVars($sql, ':languageId', $languageId, 'integer');
        $results = $db->Execute($sql);

        $coupon = null;
        if (0 < $results->RecordCount()) {
            $coupon = $this->_newCoupon($results->fields);
        }

        return $coupon;
    }

    /**
     * Coupon lookup for the given id.
     *
     * @param int id The coupon id.
     * @param int languageId The languageId; default is <code>null</code> for session language.
     * @return ZMCoupon A <code>ZMCoupon</code> instance or <code>null</code>.
     */
    function getCouponForId($id, $languageId=null) {
        if (null === $languageId) {
            $session = ZMRequest::getSession();
            $languageId = $session->getLanguageId();
        }

        $db = ZMRuntime::getDB();
        $sql = "select c.coupon_id, c.coupon_code, c.coupon_type, c.coupon_amount, c.coupon_minimum_order, c.coupon_start_date,
                c.coupon_expire_date, c.uses_per_coupon, c.uses_per_user,
                cd.coupon_name, cd.coupon_description
                from " . TABLE_COUPONS . " c
                left join " . TABLE_COUPONS_DESCRIPTION . " cd
                on (c.coupon_id = cd.coupon_id
                and cd.language_id = :languageId)
                where c.coupon_id = :id";
        $sql = $db->bindVars($sql, ':id', $id, 'integer');
        $sql = $db->bindVars($sql, ':languageId', $languageId, 'integer');
        $results = $db->Execute($sql);

        $coupon = null;
        if (0 < $results->RecordCount()) {
            $coupon = $this->_newCoupon($results->fields);
        }

        return $coupon;
    }

    /**
     * Get the coupon/voucher balance for the given account.
     *
     * @param int accountId The account id.
     * @return float The available balance or <code>0</code>.
     */
    function getVoucherBalanceForAccountId($accountId) {
        $db = ZMRuntime::getDB();
        $sql = "select amount from " . TABLE_COUPON_GV_CUSTOMER . "
                where customer_id = :accountId";
        $sql = $db->bindVars($sql, ":accountId", $accountId, "integer");

        $results = $db->Execute($sql);
        if (!$results->EOF) {
            return $results->fields['amount'];
        }

        return 0;
    }

    /**
     * Update the coupon/coucher balance for the given account id.
     *
     * @param int accountId The account id.
     * @param float amount The new amount.
     */
    function setVoucherBalanceForAccountId($accountId, $amount) {
        $db = ZMRuntime::getDB();
        $sql = "update " . TABLE_COUPON_GV_CUSTOMER . "
                set amount = :amount
                where customer_id = :accountId";
        $sql = $db->bindVars($sql, ':amount', $amount, 'currency');
        $sql = $db->bindVars($sql, ':accountId', $accountId, 'integer');
        $db->Execute($sql);
    }

    /**
     * Create a new coupon.
     *
     * @param int accountId The account id.
     * @param float amount The new amount.
     * @param string type The coupon type.
     * @return ZMCoupon A <code>ZMCoupon</code> instance or <code>null</code>.
     */
    function createCoupon($couponCode, $amount, $type) {
        $db = ZMRuntime::getDB();
        $sql = "insert into " . TABLE_COUPONS . " (coupon_type, coupon_code, date_created, coupon_amount)
                values (:type, :couponCode, now(), :amount)";
        $sql = $db->bindVars($sql, ':type', $type, 'string');
        $sql = $db->bindVars($sql, ':couponCode', $couponCode, 'string');
        $sql = $db->bindVars($sql, ':amount', $amount, 'currency');
        $results = $db->Execute($sql);

        $id = $db->Insert_ID();
        $coupon = ZMLoader::make("Coupon", $id, $couponCode, $type);
        $coupon->amount_ = $fields['coupon_amount'];

        return $coupon;
    }

    /**
     * Create a new coupon tracker record.
     *
     * @param ZMCoupon coupon The coupon.
     * @param ZMAccount account The sender account.
     * @param ZMGVReceiver gvreceiver The receiver.
     */
    function createCouponTracker($coupon, $account, $gvreceiver) {
        $db = ZMRuntime::getDB();
        $sql = "insert into " . TABLE_COUPON_EMAIL_TRACK . "(coupon_id, customer_id_sent, sent_firstname, sent_lastname, emailed_to, date_sent)
                 values (:couponId, :accountId, :firstName, :lastName, :email, now())";
        $sql = $db->bindVars($sql, ':couponId', $coupon->getId(), 'integer');
        $sql = $db->bindVars($sql, ':accountId', $account->getId(), 'integer');
        $sql = $db->bindVars($sql, ':firstName', $account->getFirstName(), 'string');
        $sql = $db->bindVars($sql, ':lastName', $account->getLastName(), 'string');
        $sql = $db->bindVars($sql, ':email', $gvreceiver->getEmail(), 'string');
        $db->Execute($sql);
    }

    /**
     * Check if a given coupon code can be redeemed.
     *
     * @param string couponId The coupon id to verify.
     * @return boolean <code>true</code> if the coupon can be redeemed, <code>false</code> if not.
     */
    function isCouponRedeemable($couponId) {
        $db = ZMRuntime::getDB();
        $sql = "select coupon_id from ". TABLE_COUPON_REDEEM_TRACK . " where coupon_id = :couponId";
        $sql = $db->bindVars($sql, ':couponId', $couponId, 'integer');
        $results = $db->Execute($sql);

        return 0 == $results->RecordCount();
    }

    /**
     * Redeem a coupon.
     *
     * <p>This will call <code>finalizeCoupon(...)</code> and <code>creditCoupon(...)</code>.</p>
     *
     * @param int couponId The coupon id.
     * @param int accountId The redeeming account id.
     * @param string remoteIp The redeeming IP addres; default is an empty string.
     */
    function redeemCoupon($couponId, $accountId, $remoteIp='') {
        $this->finalizeCoupon($couponId, $accountId, $removeIp);
        $this->creditCoupon($couponId, $accountId);
    }

    /**
     * Finalize a coupon.
     *
     * @param int couponId The coupon id.
     * @param int accountId The redeeming account id.
     * @param string remoteIp The redeeming IP addres; default is an empty string.
     */
    function finalizeCoupon($couponId, $accountId, $remoteIp='') {
        $db = ZMRuntime::getDB();
        $sql = "insert into  " . TABLE_COUPON_REDEEM_TRACK . "(coupon_id, customer_id, redeem_date, redeem_ip)
                values (:couponId, :accountId, now(), :remoteAddr)";

        $sql = $db->bindVars($sql, ':couponId', $couponId, 'integer');
        $sql = $db->bindVars($sql, ':accountId', $accountId, 'integer');
        $sql = $db->bindVars($sql, ':remoteAddr', $remoteIp, 'string');
        $db->Execute($sql);

        $sql = "update " . TABLE_COUPONS . " set coupon_active = 'N' where coupon_id = :couponId";
        $sql = $db->bindVars($sql, ':couponId', $couponId, 'integer');
        $db->Execute($sql);
    }

    /**
     * Credit coupon for account.
     *
     * @param int couponId The coupon id.
     * @param int accountId The redeeming account id.
     */
    function creditCoupon($couponId, $accountId) {
        $db = ZMRuntime::getDB();

        // get coupon value
        $sql = "select coupon_amount
                from " . TABLE_COUPONS . "
                where coupon_id = :couponId";
        $sql = $db->bindVars($sql, ':couponId', $couponId, 'integer');
        $results = $db->Execute($sql);
        $couponValue = $results->fields['coupon_amount'];

        // check if customer has already a balance
        $sql = "select amount
                from " . TABLE_COUPON_GV_CUSTOMER . "
                where customer_id = :accountId";
        $sql = $db->bindVars($sql, ':accountId', $accountId, 'integer');

        $results = $db->Execute($sql);
        if ($results->RecordCount() > 0) {
            $newAmount = $results->fields['amount'] + $couponValue;
            $sql = "update " . TABLE_COUPON_GV_CUSTOMER . "
                   set amount = :newAmount where customer_id = :accountId";
            $sql = $db->bindVars($sql, ':newAmount', $newAmount, 'float');
            $sql = $db->bindVars($sql, ':accountId', $accountId, 'integer');
            $db->Execute($sql);
        } else {
            $sql = "insert into " . TABLE_COUPON_GV_CUSTOMER . " (customer_id, amount)
                    values (:accountId, :couponValue)";
            $sql = $db->bindVars($sql, ':couponValue', $couponValue, 'float');
            $sql = $db->bindVars($sql, ':accountId', $accountId, 'integer');
            $db->Execute($sql);
        }
    }

    /**
     * Create a new coupon code.
     *
     * @param string salt The salt to be used to generate the unique code.
     * @param int length The coupon code length; default is <em>0</em> to use the setting <em>couponCodeLength</em>.
     * @return string A new unique coupon code.
     */
    function createCouponCode($salt, $length=0) {
        $length = 0 == $length ? ZMSettings::get('couponCodeLength') : $length;

        srand((double)microtime()*1000000); 
        $codes = md5(uniqid(@rand().$salt, true));
        $codes .= md5(uniqid($salt, true));
        $codes .= md5(uniqid($salt.@rand(), false));
        $codes .= md5(uniqid($salt, true));

        $db = ZMRuntime::getDB();
        for ($ii=@rand(0, 64); $ii+$length < 128; ++$i) {
            $code = substr($codes, $ii, $length);

            $sql = "select coupon_code
                    from " . TABLE_COUPONS . "
                    where coupon_code = :code";
            $sql = $db->bindVars($sql, ':code', $code, 'string');
            $results = $db->Execute($sql);
            if (0 == $results->RecordCount()) {
                return $code;
            }
        }
        $this->log('could not create coupon code', ZM_LOG_ERROR);
        return null;
    }

    /**
     * Create new coupon instance.
     */
    function _newCoupon($fields) {
        $coupon = ZMLoader::make("Coupon", $fields['coupon_id'], $fields['coupon_code'], $fields['coupon_type']);
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

    /**
     * Load coupon restrictions for the given coupon id.
     */
    function _getRestrictionsForId($id) {
        $db = ZMRuntime::getDB();
        $sql = "select * from " . TABLE_COUPON_RESTRICT . "
                where coupon_id = :id";
        $sql = $db->bindVars($sql, ':id', $id, 'string');
        $results = $db->Execute($sql);

        $restrictions = ZMLoader::make("CouponRestrictions");
        $products = array();
        while (!$results->EOF) {
            if (0 != $results->fields['category_id']) {
                $restriction = ZMLoader::make("CategoryCouponRestriction", $results->fields['coupon_restrict'] == 'N', $results->fields['category_id']);
                $categories[] = $restriction;
            } else {
                $restriction = ZMLoader::make("ProductCouponRestriction", $results->fields['coupon_restrict'] == 'N', $results->fields['product_id']);
                $products[] = $restriction;
            }
            $results->MoveNext();
        }

        return ZMLoader::make("CouponRestrictions", $categories, $products);
    }

}

?>
