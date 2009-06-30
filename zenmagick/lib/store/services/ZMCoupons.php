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
 */
?>
<?php


/**
 * Coupons.
 *
 * @author DerManoMann
 * @package org.zenmagick.store.services
 * @version $Id: ZMCoupons.php 2149 2009-04-13 22:59:14Z dermanomann $
 */
class ZMCoupons extends ZMObject {
    const BALANCE_SET = 'balance_set';
    const BALANCE_ADD = 'balance_add';
    const TYPPE_GV = 'G';
    const TYPPE_FIXED = 'F';
    const TYPPE_PERCENT = 'P';
    const TYPPE_SHIPPING = 'S';
    private $countries_;


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
    public function getCouponForCode($code, $languageId=null) {
        if (null === $languageId) {
            $session = ZMRequest::getSession();
            $languageId = $session->getLanguageId();
        }

        // XXX: relies on order of selected columns; (coupon_id returned twice and cd might be NULL if no description!)
        $sql = "SELECT cd.*, c.*
                FROM " . TABLE_COUPONS . " c
                  LEFT JOIN " . TABLE_COUPONS_DESCRIPTION . " cd ON (c.coupon_id = cd.coupon_id AND cd.language_id = :languageId)
                WHERE c.coupon_code = :code";
        $args = array('code' => $code, 'languageId' => $languageId);
        return Runtime::getDatabase()->querySingle($sql, $args, array(TABLE_COUPONS, TABLE_COUPONS_DESCRIPTION), 'Coupon');
    }

    /**
     * Coupon lookup for the given id.
     *
     * @param int id The coupon id.
     * @param int languageId The languageId; default is <code>null</code> for session language.
     * @return ZMCoupon A <code>ZMCoupon</code> instance or <code>null</code>.
     */
    public function getCouponForId($id, $languageId=null) {
        if (null === $languageId) {
            $session = ZMRequest::getSession();
            $languageId = $session->getLanguageId();
        }

        // XXX: relies on order of selected columns; (coupon_id returned twice and cd might be NULL if no description!)
        $sql = "SELECT cd.*, c.*
                FROM " . TABLE_COUPONS . " c
                  LEFT JOIN " . TABLE_COUPONS_DESCRIPTION . " cd ON (c.coupon_id = cd.coupon_id AND cd.language_id = :languageId)
                WHERE c.coupon_id = :couponId";
        $args = array('couponId' => $id, 'languageId' => $languageId);
        return Runtime::getDatabase()->querySingle($sql, $args, array(TABLE_COUPONS, TABLE_COUPONS_DESCRIPTION), 'Coupon');
    }

    /**
     * Get the coupon/voucher balance for the given account.
     *
     * @param int accountId The account id.
     * @return float The available balance or <code>0</code>.
     */
    public function getVoucherBalanceForAccountId($accountId) {
        $sql = "SELECT amount from " . TABLE_COUPON_GV_CUSTOMER . "
                WHERE customer_id = :accountId";
        $result = Runtime::getDatabase()->querySingle($sql, array('accountId' => $accountId), TABLE_COUPON_GV_CUSTOMER);
        return null !== $result ? $result['amount'] : 0;
    }

    /**
     * Update the coupon/coucher balance for the given account id.
     *
     * @param int accountId The account id.
     * @param float amount The new amount.
     */
    public function setVoucherBalanceForAccountId($accountId, $amount) {
        $this->updateVoucherBalanceForAccountId($accountId, $amount, ZMCoupons::BALANCE_SET);
    }

    /**
     * Update the coupon/coucher balance for the given account id.
     *
     * @param int accountId The account id.
     * @param float amount The new amount.
     * @param string mode Optional update mode; either <code>BALANCE_SET</code> or <code>BALANCE_ADD</code>.
     */
    protected function updateVoucherBalanceForAccountId($accountId, $amount, $mode=ZMCoupons::BALANCE_SET) {
        // check if customer has already a balance
        $sql = "SELECT amount
                FROM " . TABLE_COUPON_GV_CUSTOMER . "
                WHERE customer_id = :accountId";
        $result = Runtime::getDatabase()->querySingle($sql, array('accountId' => $accountId), TABLE_COUPON_GV_CUSTOMER);
        if (null != $result) {
            if (ZMCoupons::BALANCE_ADD == $mode) {
                $amount = $result['amount'] + $amount;
            }
            $sql = "UPDATE " . TABLE_COUPON_GV_CUSTOMER . "
                    SET amount = :amount
                    WHERE customer_id = :accountId";
        } else {
            $sql = "INSERT INTO " . TABLE_COUPON_GV_CUSTOMER . " (customer_id, amount)
                    VALUES (:accountId, :amount)";
        }
        Runtime::getDatabase()->update($sql, array('accountId' => $accountId, 'amount' => $amount), TABLE_COUPON_GV_CUSTOMER);
    }

    /**
     * Create a new coupon.
     *
     * @param int accountId The account id.
     * @param float amount The new amount.
     * @param string type The coupon type.
     * @return ZMCoupon A <code>ZMCoupon</code> instance or <code>null</code>.
     */
    public function createCoupon($couponCode, $amount, $type) {
        $coupon = ZMLoader::make("Coupon", 0, $couponCode, $type);
        $coupon->setAmount($amount);
        return Runtime::getDatabase()->createModel(TABLE_COUPONS, $coupon);
    }

    /**
     * Create a new coupon tracker record.
     *
     * @param ZMCoupon coupon The coupon.
     * @param ZMAccount account The sender account.
     * @param ZMGVReceiver gvreceiver The receiver.
     */
    public function createCouponTracker($coupon, $account, $gvreceiver) {
        $tracker = new ZMObject();
        $tracker->set('couponId', $coupon->getId());
        $tracker->set('accountId', $account->getId());
        $tracker->set('firstName', $account->getFirstName());
        $tracker->set('lastName', $account->getLastName());
        $tracker->set('emailTo', $gvreceiver->getEmail());
        $tracker->set('dateSent', date(ZMDatabase::DATETIME_FORMAT));
        Runtime::getDatabase()->createModel(TABLE_COUPON_EMAIL_TRACK, $tracker);
    }

    /**
     * Check if a given coupon code can be redeemed.
     *
     * @param string couponId The coupon id to verify.
     * @return boolean <code>true</code> if the coupon can be redeemed, <code>false</code> if not.
     */
    public function isCouponRedeemable($couponId) {
        $sql = "SELECT coupon_id FROM ". TABLE_COUPON_REDEEM_TRACK . "
                WHERE coupon_id = :couponId";
        $results = Runtime::getDatabase()->query($sql, array('couponId' => $couponId), TABLE_COUPON_REDEEM_TRACK, ZMDatabase::MODEL_RAW);
        return 0 == count($results);
    }

    /**
     * Redeem a coupon.
     *
     * <p>This will call <code>finaliseCoupon(...)</code> and <code>creditCoupon(...)</code>.</p>
     *
     * @param int couponId The coupon id.
     * @param int accountId The redeeming account id.
     * @param string remoteIp The redeeming IP addres; default is an empty string.
     */
    public function redeemCoupon($couponId, $accountId, $remoteIp='') {
        $this->finaliseCoupon($couponId, $accountId, $remoteIp);
        $this->creditCoupon($couponId, $accountId);
    }

    /**
     * Finalise a coupon.
     *
     * @param int couponId The coupon id.
     * @param int accountId The redeeming account id.
     * @param string remoteIp The redeeming IP addres; default is an empty string.
     */
    public function finaliseCoupon($couponId, $accountId, $remoteIp='') {
        $tracker = new ZMObject();
        $tracker->set('couponId', $couponId);
        $tracker->set('accountId', $accountId);
        $tracker->set('redeemDate', date(ZMDatabase::DATETIME_FORMAT));
        $tracker->set('redeemIp', $remoteIp);
        $tracker->set('orderId', 0);
        Runtime::getDatabase()->createModel(TABLE_COUPON_REDEEM_TRACK, $tracker);

        $sql = "UPDATE " . TABLE_COUPONS . " 
                SET coupon_active = :active
                WHERE coupon_id = :couponId";
        $args = array('couponId' => $couponId, 'active' => 'N');
        Runtime::getDatabase()->update($sql, $args, TABLE_COUPONS);
    }

    /**
     * Get a coupon queue entry.
     *
     * @param int queueId The coupon queue id.
     * @return ZMCouponQueue A queue entry or <code>null</code>.
     */
    public function getCouponQueueEntryForId($queueId) {
        $sql = "SELECT * 
                FROM " . TABLE_COUPON_GV_QUEUE . "
                WHERE unique_id = :id";
        return Runtime::getDatabase()->querySingle($sql, array('id' => $queueId), TABLE_COUPON_GV_QUEUE, 'CouponQueue');
    }

    /**
     * Credit coupon for account.
     *
     * @param int couponId The coupon id.
     * @param int accountId The redeeming account id.
     */
    public function creditCoupon($couponId, $accountId) {
        // get coupon value
        $sql = "SELECT coupon_amount
                FROM " . TABLE_COUPONS . "
                WHERE coupon_id = :couponId";
        $result = Runtime::getDatabase()->querySingle($sql, array('couponId' => $couponId), TABLE_COUPONS);
        $this->updateVoucherBalanceForAccountId($accountId, $result['amount'], ZMCoupons::BALANCE_ADD);
    }

    /**
     * Create a new coupon code.
     *
     * @param string salt The salt to be used to generate the unique code.
     * @param int length The coupon code length; default is <em>0</em> to use the setting <em>couponCodeLength</em>.
     * @return string A new unique coupon code.
     */
    public function createCouponCode($salt, $length=0) {
        $length = 0 == $length ? ZMSettings::get('couponCodeLength') : $length;

        srand((double)microtime()*1000000); 
        $codes = md5(uniqid(@rand().$salt, true));
        $codes .= md5(uniqid($salt, true));
        $codes .= md5(uniqid($salt.@rand(), false));
        $codes .= md5(uniqid($salt, true));

        for ($ii=@rand(0, 64); $ii+$length < 128; ++$i) {
            $code = substr($codes, $ii, $length);

            $sql = "SELECT coupon_code
                    FROM " . TABLE_COUPONS . "
                    WHERE coupon_code = :code";
            $results = Runtime::getDatabase()->query($sql, array('code' => $code), TABLE_COUPONS, ZMDatabase::MODEL_RAW);
            if (0 == count($results)) {
                return $code;
            }
        }
        ZMLogging::instance()->log('could not create coupon code', ZMLogging::ERROR);
        return null;
    }

    /**
     * Load coupon restrictions for the given coupon id.
     *
     * @param int id The coupon id.
     * @return ZMCouponRestrictions The restrictions.
     */
    public function getRestrictionsForCouponId($couponId) {
        $sql = "SELECT * FROM " . TABLE_COUPON_RESTRICT . "
                WHERE coupon_id = :couponId";
        $results = Runtime::getDatabase()->query($sql, array('couponId' => $couponId), TABLE_COUPON_RESTRICT);

        $restrictions = ZMLoader::make("CouponRestrictions");
        $products = array();
        foreach ($results as $result) {
            if (0 != $result['categoryId']) {
                $restriction = ZMLoader::make("CategoryCouponRestriction", $result['restriction'] == 'N', $result['categoryId']);
                $categories[] = $restriction;
            } else {
                $restriction = ZMLoader::make("ProductCouponRestriction", $result['restriction'] == 'N', $result['productId']);
                $products[] = $restriction;
            }
        }

        return ZMLoader::make("CouponRestrictions", $categories, $products);
    }

}

?>
