<?php

/**
 * Test accounts service.
 *
 * @package org.zenmagick.plugins.zm_tests.tests
 * @author DerManoMann
 * @version $Id$
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
            'Type' => ZMAccounts::REGISTERED,
            'PriceGroupId' => 0,
    );


    /**
     * Create test account.
     */
    public function createAccount($data) {
        $account = ZMLoader::make('Account');
        foreach ($data as $key => $value) {
            if ('Dob' == $key) {
                $value = ZMTools::translateDateString($value, 'dd/mm/yyyy', ZM_DATETIME_FORMAT);
            }
            $method = 'set'.$key;
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
            $getter = 'get'.$key;
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
            $getter = 'get'.$key;
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
            $getter = 'get'.$key;
            $this->assertEqual($account->$getter(), $reloaded->$getter(), '%s getter='.$getter);
        }
    }

    /**
     * Test get accounts.
     */
    public function testGetAccountsForEmail() {
        // gets us at least two guest accounts
        $account1 = $this->createAccount($this->accountData1);
        $account1->setType(ZMAccounts::GUEST);
        $account1 = ZMAccounts::instance()->createAccount($account1);
        $account2 = $this->createAccount($this->accountData1);
        $account2->setType(ZMAccounts::GUEST);
        $account2 = ZMAccounts::instance()->createAccount($account2);

        $accounts = ZMAccounts::instance()->getAccountsForEmailAddress($account2->getEmail());
        $this->assertTrue(2 == count($accounts));
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

?>
