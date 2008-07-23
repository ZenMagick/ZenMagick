<?php

/**
 * Test accounts service.
 *
 * @package org.zenmagick.plugins.zm_tests.tests
 * @author DerManoMann
 * @version $Id$
 */
class TestZMAccounts extends UnitTestCase {
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
            'Type' => ZM_ACCOUNT_TYPE_REGISTERED,
            'PriceGroupId' => 0,
    );


    /**
     * Test create account.
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
     * Set up.
     */
    public function setUp() {
        ZMSettings::set('dbProvider', 'ZMCreoleDatabase');
        //ZMSettings::set('dbProvider', 'ZMZenCartDatabase');
    }

    /**
     * Clean up.
     */
    public function tearDown() {
        $sql = 'SELECT customers_id FROM '.TABLE_CUSTOMERS.' WHERE customers_lastname = \'doe\'';
        $results = ZMRuntime::getDatabase()->query($sql, array(), TABLE_CUSTOMERS);
        $ids = array();
        foreach ($results as $result) {
            $ids[] = $result['id'];
        }

        $sql = 'DELETE FROM '.TABLE_CUSTOMERS_INFO.' WHERE customers_info_id IN (:accountId)';
        $results = ZMRuntime::getDatabase()->update($sql, array('accountId' => $ids), TABLE_CUSTOMERS_INFO);

        $sql = 'DELETE FROM '.TABLE_CUSTOMERS.' WHERE customers_id IN (:id)';
        $results = ZMRuntime::getDatabase()->update($sql, array('id' => $ids), TABLE_CUSTOMERS);
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
            $this->assertEqual($account->$getter(), $reloaded->$getter());
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
            $this->assertEqual($account->$getter(), $reloaded->$getter());
        }
    }

}

?>
