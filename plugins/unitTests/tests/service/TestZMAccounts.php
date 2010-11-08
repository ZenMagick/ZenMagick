<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2010 zenmagick.org
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
 * Test accounts service.
 *
 * @package org.zenmagick.plugins.unitTests.tests
 * @author DerManoMann
 */
class TestZMAccounts extends ZMTestCase {
    // test account data
    protected $accountData1 = array(
            'FirstName' => 'john',
            'LastName' => 'doe',
            'Dob' => '16/11/1967',
            'NickName' => 'johnd',
            'Gender' => 'm',
            'Email' => 'john.doe@somewhere.com',
            'Phone' => '03 333 3333',
            'Fax' => '',
            'EmailFormat' => 'TEXT',
            'Referral' => '',
            'Password' => 'myprecious',
            'Authorization' => 0,
            'NewsletterSubscriber' => false,
            'GlobalProductSubscriber' => true,
            'SubscribedProducts' => null,
            'Type' => ZMAccount::REGISTERED,
            'PriceGroupId' => 0,
    );


    /**
     * Create test account.
     */
    public function createAccount($data) {
        $account = ZMBeanUtils::getBean('Account');
        foreach ($data as $key => $value) {
            if ('Dob' == $key) {
                $value = ZMTools::translateDateString($value, 'dd/mm/yyyy', ZM_DATETIME_FORMAT);
            }
            $method = 'set'.ucwords($key);
            $account->$method($value);
        }
        return $account;
    }

    /**
     * Clean up.
     */
    public function tearDown() {
        $sql = 'SELECT customers_id FROM '.TABLE_CUSTOMERS.' WHERE customers_lastname = \'doe\'';
        $results = ZMRuntime::getDatabase()->query($sql, array(), TABLE_CUSTOMERS);
        $ids = array();
        foreach ($results as $result) {
            $ids[] = $result['accountId'];
        }

        if (0 == count($ids)) {
            // nothing to do
            return;
        }

        $sql = 'DELETE FROM '.TABLE_CUSTOMERS_INFO.' WHERE customers_info_id IN (:accountId)';
        $results = ZMRuntime::getDatabase()->update($sql, array('accountId' => $ids), TABLE_CUSTOMERS_INFO);

        $sql = 'DELETE FROM '.TABLE_CUSTOMERS.' WHERE customers_id IN (:accountId)';
        $results = ZMRuntime::getDatabase()->update($sql, array('accountId' => $ids), TABLE_CUSTOMERS);
        parent::tearDown();
    }


    /**
     * Test create account.
     */
    public function testCreateAccount() {
        $account = $this->createAccount($this->accountData1);
        $account = ZMAccounts::instance()->createAccount($account);
        $this->assertNotEqual(0, $account->getId());
        $reloaded = ZMAccounts::instance()->getAccountForId($account->getId());
        foreach (array_keys($this->accountData1) as $key) {
            $getter = 'get'.ucwords($key);
            $this->assertEqual($account->$getter(), $reloaded->$getter(), '%s getter='.$getter);
        }
    }

    /**
     * Test create account no DOB.
     */
    public function testCreateAccountNoDOB() {
        $account = $this->createAccount($this->accountData1);
        $account->setDob(null);
        $account = ZMAccounts::instance()->createAccount($account);
        $this->assertNotEqual(0, $account->getId());
        $reloaded = ZMAccounts::instance()->getAccountForId($account->getId());
        foreach (array_keys($this->accountData1) as $key) {
            $getter = 'get'.ucwords($key);
            $this->assertEqual($account->$getter(), $reloaded->$getter(), '%s getter='.$getter);
        }
    }

    /**
     * Test update account.
     */
    public function testUpdateAccount() {
        $account = $this->createAccount($this->accountData1);
        $account = ZMAccounts::instance()->createAccount($account);
        $this->assertNotEqual(0, $account->getId());
        $account->setFirstName('foo');
        ZMAccounts::instance()->updateAccount($account);
        $reloaded = ZMAccounts::instance()->getAccountForId($account->getId());
        foreach (array_keys($this->accountData1) as $key) {
            $getter = 'get'.ucwords($key);
            $this->assertEqual($account->$getter(), $reloaded->$getter(), '%s getter='.$getter);
        }
    }

    /**
     * Test get accounts.
     */
    public function testGetAccountsForEmail() {
        // gets us at least two guest accounts
        $account1 = $this->createAccount($this->accountData1);
        $account1->setType(ZMAccount::GUEST);
        $account1 = ZMAccounts::instance()->createAccount($account1);
        $account2 = $this->createAccount($this->accountData1);
        $account2->setType(ZMAccount::GUEST);
        $account2 = ZMAccounts::instance()->createAccount($account2);

        $accounts = ZMAccounts::instance()->getAccountsForEmailAddress($account2->getEmail());
        $this->assertEqual(2, count($accounts));
    }

    /**
     * Test products subscriptions.
     */
    public function testProductSubscriptions() {
        // delete previous subscriptions
        $sql = "DELETE from " . TABLE_PRODUCTS_NOTIFICATIONS . "
                WHERE  customers_id = :accountId";
        ZMRuntime::getDatabase()->update($sql, array('accountId' => 2), TABLE_PRODUCTS_NOTIFICATIONS);

        $testProductIds = array(1, 4, 7);
        // insert new
        $sql = "INSERT into " . TABLE_PRODUCTS_NOTIFICATIONS . "
                (products_id, customers_id) VALUES(:productId, :accountId)";
        foreach ($testProductIds as $id) {
            ZMRuntime::getDatabase()->update($sql, array('accountId' => 2, 'productId' => $id), TABLE_PRODUCTS_NOTIFICATIONS);
        }

        $subscribedProductIds = ZMAccounts::instance()->getSubscribedProductIds(2);
        foreach ($testProductIds as $id) {
            $this->assertTrue(in_array($id, $subscribedProductIds));
        }
    }

}
