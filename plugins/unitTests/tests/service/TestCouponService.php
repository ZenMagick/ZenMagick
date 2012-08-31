<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2012 zenmagick.org
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

use ZenMagick\Base\Beans;
use ZenMagick\apps\store\Model\Coupons\Coupon;
use ZenMagick\plugins\unitTests\simpletest\TestCase;

/**
 * Test coupon service.
 *
 * @package org.zenmagick.plugins.unitTests.tests
 * @author DerManoMann <mano@zenmagick.org>
 */
class TestCouponService extends TestCase {
    private $createdCouponIds_;
    private $testCouponId_;


    /**
     * {@inheritDoc}
     */
    public function setUp() {
        parent::setUp();

        $couponService = $this->container->get('couponService');
        $this->createdCouponIds_ = array();
        $this->accountIds_ = array($this->getAccountId());
        // create one basic test coupon
        $couponCode = $couponService->createCouponCode('foo@bar.com');
        $this->assertNotNull($couponCode);
        $coupon = $couponService->createCoupon($couponCode, 5, Coupon::TYPPE_GV);
        $this->createdCouponIds_[] = $coupon->getId();
        $this->testCouponId_ = $coupon->getId();
    }

    /**
     * {@inheritDoc}
     */
    public function tearDown() {
        $couponTables = array('coupons', 'coupons_description', 'coupon_email_track', 'coupon_redeem_track', 'coupon_restrict');
        $accountTables = array('coupon_gv_customer', 'coupon_gv_queue');

        foreach ($couponTables as $table) {
            $idName = 'coupons' == $table ? 'id' : 'couponId';
            $sql = "DELETE FROM %table.". $table."%
                    WHERE coupon_id = :".$idName;
            foreach ($this->createdCouponIds_ as $couponId) {
                ZMRuntime::getDatabase()->updateObj($sql, array($idName => $couponId), $table);
            }
        }

        foreach ($accountTables as $table) {
            $sql = "DELETE FROM %table.".$table."%
                    WHERE customer_id = :accountId";
            foreach ($this->accountIds_ as $accountId) {
                ZMRuntime::getDatabase()->updateObj($sql, array('accountId' => $accountId), $table);
            }
        }
        parent::tearDown();
    }


    /**
     * Get the test account id.
     *
     * @return int An account id.
     */
    protected function getAccountId() {
        $account = $this->container->get('accountService')->getAccountForEmailAddress('root@localhost');
        return null != $account ? $account->getId() : 1;
    }

    /**
     * Test create coupon code.
     */
    public function testCreateCouponCode() {
        $couponCode = $this->container->get('couponService')->createCouponCode('foo@bar.com');
        $this->assertNotNull($couponCode);
    }

    /**
     * Test create coupon.
     */
    public function testCreateCoupon() {
        $couponService = $this->container->get('couponService');

        $couponCode = $couponService->createCouponCode('foo@bar.com');
        $this->assertNotNull($couponCode);
        $coupon = $couponService->createCoupon($couponCode, 5, Coupon::TYPPE_GV);
        $this->createdCouponIds_[] = $coupon->getId();
        $this->assertNotNull($coupon);
        $this->assertEqual($couponCode, $coupon->getCode());
        $this->assertEqual(5, $coupon->getAmount());
    }

    /**
     * Test get coupon for code.
     */
    public function testGetCouponForCode() {
        $couponService = $this->container->get('couponService');

        $couponCode = $couponService->createCouponCode('foo@bar.com');
        $this->assertNotNull($couponCode);
        $coupon = $couponService->createCoupon($couponCode, 5, Coupon::TYPPE_GV);
        $this->createdCouponIds_[] = $coupon->getId();
        $loaded = $couponService->getCouponForCode($couponCode, 1);
        $this->assertEqual($coupon->getId(), $loaded->getId());
        $this->assertEqual($coupon->getCode(), $loaded->getCode());
        $this->assertEqual($coupon->getAmount(), $loaded->getAmount());
    }

    /**
     * Test get coupon for id.
     */
    public function testGetCouponForId() {
        $couponService = $this->container->get('couponService');

        $couponCode = $couponService->createCouponCode('foo@bar.com');
        $this->assertNotNull($couponCode);
        $coupon = $couponService->createCoupon($couponCode, 5, Coupon::TYPPE_GV);
        $this->createdCouponIds_[] = $coupon->getId();
        $loaded = $couponService->getCouponForId($coupon->getId(), 1);
        $this->assertEqual($coupon->getId(), $loaded->getId());
        $this->assertEqual($coupon->getCode(), $loaded->getCode());
        $this->assertEqual($coupon->getAmount(), $loaded->getAmount());
    }

    /**
     * Test get voucher balance for id.
     */
    public function testGetVoucherBalance() {
        $couponService = $this->container->get('couponService');

        $couponService->setVoucherBalanceForAccountId($this->getAccountId(), 141);
        $balance = $couponService->getVoucherBalanceForAccountId($this->getAccountId());
        $this->assertEqual(141, $balance);
    }

    /**
     * Test set voucher balance for id.
     */
    public function testSetVoucherBalance() {
        $couponService = $this->container->get('couponService');

        $couponService->setVoucherBalanceForAccountId($this->getAccountId(), 39);
        $balance = $couponService->getVoucherBalanceForAccountId($this->getAccountId());
        $this->assertEqual(39, $balance);
    }

    /**
     * Test restrictions.
     */
    public function testRestrictions() {
        $couponService = $this->container->get('couponService');

        $coupon = $couponService->getCouponForId($this->testCouponId_, 1);
        if (null != $coupon) {
            $restrictions = $coupon->getRestrictions();
            $this->assertNotNull($restrictions);
            $direct = $couponService->getRestrictionsForCouponId(9);
            $this->assertNotNull($direct);
        } else {
            $this->skip('test coupon not found');
        }
    }

    /**
     * Test is redeemable.
     */
    public function testIsCouponRedeemable() {
        $couponService = $this->container->get('couponService');

        $this->assertTrue($couponService->isCouponRedeemable($this->testCouponId_));
        $this->assertTrue($couponService->isCouponRedeemable(99999));
    }

    /**
     * Test coupon tracker.
     */
    public function testCouponTracker() {
        $couponService = $this->container->get('couponService');

        $couponCode = $couponService->createCouponCode('foo@bar.com');
        $coupon = $couponService->createCoupon($couponCode, 5, Coupon::TYPPE_GV);
        $this->createdCouponIds_[] = $coupon->getId();
        $account = $this->container->get('accountService')->getAccountForId($this->getAccountId());
        $gvReceiver = Beans::getBean('ZMGVReceiver');
        $gvReceiver->setEmail('foo@bar.com');

        if (null != $account) {
            $couponService->createCouponTracker($coupon, $account, $gvReceiver);

            // manually check database
            $sql = "SELECT * FROM %table.coupon_email_track%
                    WHERE coupon_id = :couponId";
            $result = ZMRuntime::getDatabase()->querySingle($sql, array('couponId' => $coupon->getId()), 'coupon_email_track', 'ZenMagick\Base\ZMObject');
            $this->assertNotNull($result);
            $this->assertEqual('foo@bar.com', $result->getEmailTo());
        } else {
            $this->skip('no test account found');
        }
    }

    /**
     * Test finalise coupon.
     */
    public function testFinaliseCoupon() {
        $couponService = $this->container->get('couponService');

        $couponCode = $couponService->createCouponCode('foo@bar.com');
        $coupon = $couponService->createCoupon($couponCode, 5, Coupon::TYPPE_GV);
        $this->createdCouponIds_[] = $coupon->getId();
        $couponService->finaliseCoupon($coupon->getId(), $this->getAccountId(), '127.0.0.1');

        // manually check database
        $sql = "SELECT * FROM %table.coupon_redeem_track%
                WHERE coupon_id = :couponId";
        $result = ZMRuntime::getDatabase()->querySingle($sql, array('couponId' => $coupon->getId()), 'coupon_redeem_track', 'ZenMagick\Base\ZMObject');
        $this->assertNotNull($result);
        $this->assertEqual('127.0.0.1', $result->getRedeemIp());

        // check active flag
        $coupon = $couponService->getCouponForCode($couponCode, 1);
        $this->assertNotNull($coupon);
        $this->assertFalse($coupon->isActive());
    }

    /**
     * Test credit coupon.
     */
    public function testCreditCoupon() {
        $couponService = $this->container->get('couponService');

        // new coupon worth $5
        $couponCode = $couponService->createCouponCode('foo@bar.com');
        $coupon = $couponService->createCoupon($couponCode, 5, Coupon::TYPPE_GV);
        $this->createdCouponIds_[] = $coupon->getId();

        $couponService->creditCoupon($coupon->getId(), $this->getAccountId());
        $this->assertEqual(5, $couponService->getVoucherBalanceForAccountId(1));

        // delete balance record to test create
        $sql = "DELETE FROM %table.coupon_gv_customer%
                WHERE customer_id = :accountId";
        ZMRuntime::getDatabase()->updateObj($sql, array('accountId' => $this->getAccountId()), 'coupon_gv_customer');

        // new coupon worth $5
        $couponCode = $couponService->createCouponCode('foo@bar.com');
        $coupon = $couponService->createCoupon($couponCode, 5, Coupon::TYPPE_GV);
        $this->createdCouponIds_[] = $coupon->getId();

        $couponService->creditCoupon($coupon->getId(), 1);
        $this->assertEqual(5, $couponService->getVoucherBalanceForAccountId($this->getAccountId()));
    }
}
