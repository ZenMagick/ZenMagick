<?php

/**
 * Test coupon service.
 *
 * @package org.zenmagick.plugins.zm_tests.tests
 * @author DerManoMann
 * @version $Id$
 */
class TestZMCoupons extends ZMTestCase {

    /**
     * Test create coupon code.
     */
    public function testCreateCouponCode() {
        $couponCode = ZMCoupons::instance()->createCouponCode('foo@bar.com');
        $this->assertNotNull($couponCode);
    }

    /**
     * Test create coupon.
     */
    public function testCreateCoupon() {
        $couponCode = ZMCoupons::instance()->createCouponCode('foo@bar.com');
        $this->assertNotNull($couponCode);
        $coupon = ZMCoupons::instance()->createCoupon($couponCode, 5, ZM_COUPON_TYPPE_GV);
        $this->assertNotNull($coupon);
        $this->assertEqual($couponCode, $coupon->getCode());
        $this->assertEqual(5, $coupon->getAmount());
    }

    /**
     * Test get coupon for code.
     */
    public function testGetCouponForCode() {
        $couponCode = ZMCoupons::instance()->createCouponCode('foo@bar.com');
        $this->assertNotNull($couponCode);
        $coupon = ZMCoupons::instance()->createCoupon($couponCode, 5, ZM_COUPON_TYPPE_GV);
        $loaded = ZMCoupons::instance()->getCouponForCode($couponCode);
        $this->assertEqual($coupon->getId(), $loaded->getId());
        $this->assertEqual($coupon->getCode(), $loaded->getCode());
        $this->assertEqual($coupon->getAmount(), $loaded->getAmount());
    }

    /**
     * Test get coupon for id.
     */
    public function testGetCouponForId() {
        $couponCode = ZMCoupons::instance()->createCouponCode('foo@bar.com');
        $this->assertNotNull($couponCode);
        $coupon = ZMCoupons::instance()->createCoupon($couponCode, 5, ZM_COUPON_TYPPE_GV);
        $loaded = ZMCoupons::instance()->getCouponForId($coupon->getId());
        $this->assertEqual($coupon->getId(), $loaded->getId());
        $this->assertEqual($coupon->getCode(), $loaded->getCode());
        $this->assertEqual($coupon->getAmount(), $loaded->getAmount());
    }

    /**
     * Test get voucher balance for id.
     */
    public function testGetVoucherBalanceForAccountId() {
        ZMCoupons::instance()->setVoucherBalanceForAccountId(2, 141);
        $balance = ZMCoupons::instance()->getVoucherBalanceForAccountId(2);
        $this->assertEqual(141, $balance);
    }

    /**
     * Test set voucher balance for id.
     */
    public function testSetVoucherBalanceForAccountId() {
        ZMCoupons::instance()->setVoucherBalanceForAccountId(2, 39);
        $balance = ZMCoupons::instance()->getVoucherBalanceForAccountId(2);
        $this->assertEqual(39, $balance);
        ZMCoupons::instance()->setVoucherBalanceForAccountId(2, 141);
    }

    /**
     * Test restrictions.
     */
    public function testRestrictions() {
        $coupon = ZMCoupons::instance()->getCouponForId(9);
        if (null != $coupon) {
            $restrictions = $coupon->getRestrictions();
            $this->assertNotNull($restrictions);
            $direct = ZMCoupons::instance()->getRestrictionsForCouponId(9);
            $this->assertNotNull($direct);
        } else {
            $this->fail('test coupon not found');
        }
    }

    /**
     * Test is redeemable.
     */
    public function testIsCouponRedeemable() {
        $this->assertFalse(ZMCoupons::instance()->isCouponRedeemable(1));
        $this->assertTrue(ZMCoupons::instance()->isCouponRedeemable(99999));
    }

    /**
     * Test coupon tracker.
     */
    public function testCouponTracker() {
        $couponCode = ZMCoupons::instance()->createCouponCode('foo@bar.com');
        $coupon = ZMCoupons::instance()->createCoupon($couponCode, 5, ZM_COUPON_TYPPE_GV);
        $account = ZMAccounts::instance()->getAccountForId(377);
        $gvReceiver = ZMLoader::make('GVReceiver');
        $gvReceiver->setEmail('foo@bar.com');

        ZMCoupons::instance()->createCouponTracker($coupon, $account, $gvReceiver);

        // manually check database
        $sql = "SELECT * FROM " . TABLE_COUPON_EMAIL_TRACK . "
                WHERE coupon_id = :couponId";
        $result = ZMRuntime::getDatabase()->querySingle($sql, array('couponId' => $coupon->getId()), TABLE_COUPON_EMAIL_TRACK, 'Model');
        $this->assertNotNull($result);
        $this->assertEqual('foo@bar.com', $result->getEmailTo());
    }

    /**
     * Test finalize coupon.
     */
    public function testFinalizeCoupon() {
        $couponCode = ZMCoupons::instance()->createCouponCode('foo@bar.com');
        $coupon = ZMCoupons::instance()->createCoupon($couponCode, 5, ZM_COUPON_TYPPE_GV);
        ZMCoupons::instance()->finalizeCoupon($coupon->getId(), 377, '127.0.0.1');

        // manually check database
        $sql = "SELECT * FROM " . TABLE_COUPON_REDEEM_TRACK . "
                WHERE coupon_id = :couponId";
        $result = ZMRuntime::getDatabase()->querySingle($sql, array('couponId' => $coupon->getId()), TABLE_COUPON_REDEEM_TRACK, 'Model');
        $this->assertNotNull($result);
        $this->assertEqual('127.0.0.1', $result->getRedeemIp());

        // check active flag
        $coupon = ZMCoupons::instance()->getCouponForCode($couponCode);
        $this->assertNotNull($coupon);
        $this->assertFalse($coupon->isActive());
    }

    /**
     * Test credit coupon.
     */
    public function testCreditCoupon() {
        // set known balance (test balance update)
        ZMCoupons::instance()->setVoucherBalanceForAccountId(2, 141);

        // new coupon worth $5
        $couponCode = ZMCoupons::instance()->createCouponCode('foo@bar.com');
        $coupon = ZMCoupons::instance()->createCoupon($couponCode, 5, ZM_COUPON_TYPPE_GV);

        ZMCoupons::instance()->creditCoupon($coupon->getId(), 2);
        $this->assertEqual(146, ZMCoupons::instance()->getVoucherBalanceForAccountId(2));

        // delete balance record to test create
        $sql = "DELETE FROM " . TABLE_COUPON_GV_CUSTOMER . "
                WHERE customer_id = :accountId";
        ZMRuntime::getDatabase()->update($sql, array('accountId' => 2), TABLE_COUPON_GV_CUSTOMER);

        // new coupon worth $5
        $couponCode = ZMCoupons::instance()->createCouponCode('foo@bar.com');
        $coupon = ZMCoupons::instance()->createCoupon($couponCode, 5, ZM_COUPON_TYPPE_GV);

        ZMCoupons::instance()->creditCoupon($coupon->getId(), 2);
        $this->assertEqual(5, ZMCoupons::instance()->getVoucherBalanceForAccountId(2));

        // set known balance (test balance update)
        ZMCoupons::instance()->setVoucherBalanceForAccountId(2, 141);
    }
}

?>
