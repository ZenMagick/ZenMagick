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

namespace zenmagick\apps\store\services\coupons;

use ZMDatabase;
use ZMRuntime;
use zenmagick\base\ZMObject;

use zenmagick\apps\store\model\coupons\Coupon;
use zenmagick\apps\store\model\coupons\CouponQueue;
use zenmagick\apps\store\model\coupons\restrictions\CouponRestrictions;
use zenmagick\apps\store\model\coupons\restrictions\ProductCouponRestriction;
use zenmagick\apps\store\model\coupons\restrictions\CategoryCouponRestriction;

/**
 * Coupons service.
 *
 * @author DerManoMann
 */
class CouponService extends ZMObject {

    /**
     * Coupon lookup for the given code.
     *
     * @param string code The coupons code.
     * @param int languageId The languageId.
     * @return Coupon A <code>Coupon</code> instance or <code>null</code>.
     */
    public function getCouponForCode($code, $languageId) {
        // XXX: relies on order of selected columns; (coupon_id returned twice and cd might be NULL if no description!)
        $sql = "SELECT cd.*, c.*
                FROM %table.coupons% c
                  LEFT JOIN %table.coupons_description% cd ON (c.coupon_id = cd.coupon_id AND cd.language_id = :languageId)
                WHERE c.coupon_code = :code";
        $args = array('code' => $code, 'languageId' => $languageId);
        return ZMRuntime::getDatabase()->querySingle($sql, $args, array('coupons', 'coupons_description'), 'zenmagick\apps\store\model\coupons\Coupon');
    }

    /**
     * Coupon lookup for the given id.
     *
     * @param int id The coupon id.
     * @param int languageId The languageId.
     * @return Coupon A <code>Coupon</code> instance or <code>null</code>.
     */
    public function getCouponForId($id, $languageId) {
        // XXX: relies on order of selected columns; (coupon_id returned twice and cd might be NULL if no description!)
        $sql = "SELECT cd.*, c.*
                FROM %table.coupons% c
                  LEFT JOIN %table.coupons_description% cd ON (c.coupon_id = cd.coupon_id AND cd.language_id = :languageId)
                WHERE c.coupon_id = :id";
        $args = array('id' => $id, 'languageId' => $languageId);
        return ZMRuntime::getDatabase()->querySingle($sql, $args, array('coupons', 'coupons_description'), 'zenmagick\apps\store\model\coupons\Coupon');
    }

    /**
     * Get the coupon/voucher balance for the given account.
     *
     * @param int accountId The account id.
     * @return float The available balance or <code>0</code>.
     */
    public function getVoucherBalanceForAccountId($accountId) {
        $sql = "SELECT amount from %table.coupon_gv_customer%
                WHERE customer_id = :accountId";
        $result = ZMRuntime::getDatabase()->querySingle($sql, array('accountId' => $accountId), 'coupon_gv_customer');
        return null !== $result ? $result['amount'] : 0;
    }

    /**
     * Update the coupon/coucher balance for the given account id.
     *
     * @param int accountId The account id.
     * @param float amount The new amount.
     */
    public function setVoucherBalanceForAccountId($accountId, $amount) {
        $this->updateVoucherBalanceForAccountId($accountId, $amount, Coupon::BALANCE_SET);
    }

    /**
     * Update the coupon/coucher balance for the given account id.
     *
     * @param int accountId The account id.
     * @param float amount The new amount.
     * @param string mode Optional update mode; either <code>BALANCE_SET</code> or <code>BALANCE_ADD</code>.
     */
    protected function updateVoucherBalanceForAccountId($accountId, $amount, $mode=Coupon::BALANCE_SET) {
        // check if customer has already a balance
        $sql = "SELECT amount
                FROM %table.coupon_gv_customer%
                WHERE customer_id = :accountId";
        $result = ZMRuntime::getDatabase()->querySingle($sql, array('accountId' => $accountId), 'coupon_gv_customer');
        if (null != $result) {
            if (Coupon::BALANCE_ADD == $mode) {
                $amount = $result['amount'] + $amount;
            }
            $sql = "UPDATE %table.coupon_gv_customer%
                    SET amount = :amount
                    WHERE customer_id = :accountId";
        } else {
            $sql = "INSERT INTO %table.coupon_gv_customer% (customer_id, amount)
                    VALUES (:accountId, :amount)";
        }
        ZMRuntime::getDatabase()->updateObj($sql, array('accountId' => $accountId, 'amount' => $amount), 'coupon_gv_customer');
    }

    /**
     * Create a new coupon.
     *
     * @param int accountId The account id.
     * @param float amount The new amount.
     * @param string type The coupon type.
     * @return Coupon A <code>Coupon</code> instance or <code>null</code>.
     */
    public function createCoupon($couponCode, $amount, $type) {
        $coupon = new Coupon(0, $couponCode, $type);
        $coupon->setAmount($amount);
        return ZMRuntime::getDatabase()->createModel('coupons', $coupon);
    }

    /**
     * Create a new coupon tracker record.
     *
     * @param Coupon coupon The coupon.
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
        $tracker->set('dateSent', new \DateTime());
        ZMRuntime::getDatabase()->createModel('coupon_email_track', $tracker);
    }

    /**
     * Check if a given coupon code can be redeemed.
     *
     * @param string couponId The coupon id to verify.
     * @return boolean <code>true</code> if the coupon can be redeemed, <code>false</code> if not.
     */
    public function isCouponRedeemable($couponId) {
        $sql = "SELECT coupon_id FROM %table.coupon_redeem_track%
                WHERE coupon_id = :couponId";
        $results = ZMRuntime::getDatabase()->fetchAll($sql, array('couponId' => $couponId), 'coupon_redeem_track', ZMDatabase::MODEL_RAW);
        return 0 == count($results);
    }

    /**
     * Redeem a coupon.
     *
     * <p>This will call <code>finaliseCoupon(...)</code> and <code>creditCoupon(...)</code>.</p>
     *
     * @param int couponId The coupon id.
     * @param int accountId The redeeming account id.
     * @param string remoteIP The redeeming IP addres; default is an empty string.
     */
    public function redeemCoupon($couponId, $accountId, $remoteIP='') {
        $this->finaliseCoupon($couponId, $accountId, $remoteIP);
        $this->creditCoupon($couponId, $accountId);
    }

    /**
     * Finalise a coupon.
     *
     * @param int couponId The coupon id.
     * @param int accountId The redeeming account id.
     * @param string remoteIP The redeeming IP addres; default is an empty string.
     */
    public function finaliseCoupon($couponId, $accountId, $remoteIP='') {
        $tracker = new ZMObject();
        $tracker->set('couponId', $couponId);
        $tracker->set('accountId', $accountId);
        $tracker->set('redeemDate', new \DateTime());
        $tracker->set('redeemIp', $remoteIP);
        $tracker->set('orderId', 0);
        ZMRuntime::getDatabase()->createModel('coupon_redeem_track', $tracker);

        $sql = "UPDATE %table.coupons%
                SET coupon_active = :active
                WHERE coupon_id = :id";
        $args = array('id' => $couponId, 'active' => Coupon::FLAG_WAITING);
        ZMRuntime::getDatabase()->updateObj($sql, $args, 'coupons');
    }

    /**
     * Get a coupon queue entry.
     *
     * @param int queueId The coupon queue id.
     * @return CouponQueue A queue entry or <code>null</code>.
     */
    public function getCouponQueueEntryForId($queueId) {
        $sql = "SELECT *
                FROM %table.coupon_gv_queue%
                WHERE unique_id = :id";
        return ZMRuntime::getDatabase()->querySingle($sql, array('id' => $queueId), 'coupon_gv_queue', 'zenmagick\apps\store\model\coupons\CouponQueue');
    }

    /**
     * Get coupon queue entries for the given flag.
     *
     * @param string flag The flag; can be '<em>Coupon::FLAG_APPROVED</em>' for approved or '<em>Coupon::FLAG_WAITING</em>' for coupons waiting for approval.
     * @return array A list of <code>CouponQueue</code> entries.
     */
    public function getCouponsForFlag($flag=Coupon::FLAG_WAITING) {
        $sql = "SELECT *
                FROM %table.coupon_gv_queue%
                WHERE release_flag = :released";
        return ZMRuntime::getDatabase()->fetchAll($sql, array('released' => $flag), 'coupon_gv_queue', 'zenmagick\apps\store\model\coupons\CouponQueue');
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
                FROM %table.coupons%
                WHERE coupon_id = :id";
        $result = ZMRuntime::getDatabase()->querySingle($sql, array('id' => $couponId), 'coupons');
        $this->updateVoucherBalanceForAccountId($accountId, $result['amount'], Coupon::BALANCE_ADD);
    }

    /**
     * Create a new coupon code.
     *
     * @param string salt The salt to be used to generate the unique code.
     * @param int length The coupon code length; default is <em>0</em> to use the setting <em>couponCodeLength</em>.
     * @return string A new unique coupon code.
     */
    public function createCouponCode($salt, $length=0) {
        $length = 0 == $length ? $this->container->get('settingsService')->get('couponCodeLength') : $length;

        srand((double)microtime()*1000000);
        $codes = md5(uniqid(@rand().$salt, true));
        $codes .= md5(uniqid($salt, true));
        $codes .= md5(uniqid($salt.@rand(), false));
        $codes .= md5(uniqid($salt, true));

        for ($ii=@rand(0, 64); $ii+$length < 128; ++$i) {
            $code = substr($codes, $ii, $length);

            $sql = "SELECT coupon_code
                    FROM %table.coupons%
                    WHERE coupon_code = :code";
            $results = ZMRuntime::getDatabase()->fetchAll($sql, array('code' => $code), 'coupons', ZMDatabase::MODEL_RAW);
            if (0 == count($results)) {
                return $code;
            }
        }

        $this->container->get('loggingService')->error('could not create coupon code');
        return null;
    }

    /**
     * Load coupon restrictions for the given coupon id.
     *
     * @param int id The coupon id.
     * @return CouponRestrictions The restrictions.
     */
    public function getRestrictionsForCouponId($couponId) {
        $sql = "SELECT * FROM %table.coupon_restrict%
                WHERE coupon_id = :couponId";
        $results = ZMRuntime::getDatabase()->fetchAll($sql, array('couponId' => $couponId), 'coupon_restrict');

        $products = array();
        $categories = array();
        foreach ($results as $result) {
            if (0 != $result['categoryId']) {
                $restriction = new CategoryCouponRestriction($result['restriction'] == Coupon::FLAG_WAITING, $result['categoryId']);
                $categories[] = $restriction;
            } else {
                $restriction = new ProductCouponRestriction($result['restriction'] == Coupon::FLAG_WAITING, $result['productId']);
                $products[] = $restriction;
            }
        }

        return new CouponRestrictions($categories, $products);
    }

    /**
     * Get all coupons.
     *
     * @param int languageId The languageId.
     * @param boolean active Optional flag to control whether to retreive active coupons only; default is <code>true</code>.
     * @return array List of coupons.
     */
    public function getCoupons($languageId, $active=true) {
        $sql = "SELECT * FROM %table.coupons% c, %table.coupons_description% cd
                WHERE cd.coupon_id = c.coupon_id AND cd.language_id = :languageId";
        return ZMRuntime::getDatabase()->fetchAll($sql, array('languageId' => $languageId), array('coupons', 'coupons_description'), 'zenmagick\apps\store\model\coupons\Coupon');
    }

}
