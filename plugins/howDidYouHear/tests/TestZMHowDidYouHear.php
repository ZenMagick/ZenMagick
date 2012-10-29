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
use ZenMagick\plugins\unitTests\simpletest\TestCase;
use ZenMagick\StoreBundle\Entity\Account\Account;

/**
 * Test <em>How did you hear about us</em> plugin.
 *
 * @package org.zenmagick.plugins.howDidYouHear
 * @author DerManoMann <mano@zenmagick.org>
 */
class TestZMHowDidYouHear extends TestCase
{
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
            'Type' => Account::REGISTERED,
            'PriceGroupId' => 0,
            'sourceId' => 1,
            'sourceOther' => '',
    );
    // test account data
    protected $accountData2 = array(
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
            'Type' => Account::REGISTERED,
            'PriceGroupId' => 0,
            'sourceId' => 9999,
            'sourceOther' => 'other',
    );

    /**
     * Create test account.
     */
    public function createAccount($data)
    {
        $account = Beans::getBean('ZenMagick\StoreBundle\Entity\Account\Account');
        foreach ($data as $key => $value) {
            $method = 'set'.ucwords($key);
            if ('Dob' == $key) {
                $value = DateTime::createFromFormat('d/m/y', $value);
            }
            $account->$method($value);
        }

        return $account;
    }

    /**
     * Clean up.
     */
    public function tearDown()
    {
        $sql = 'SELECT customers_id FROM %table.customers% WHERE customers_lastname = \'doe\'';
        $results = ZMRuntime::getDatabase()->fetchAll($sql, array(), 'customers');
        $ids = array();
        foreach ($results as $result) {
            $ids[] = $result['accountId'];
        }

        if (0 == count($ids)) {
            // nothing to do
            return;
        }

        $sql = 'DELETE FROM %table.customers_info% WHERE customers_info_id IN (:accountId)';
        $results = ZMRuntime::getDatabase()->updateObj($sql, array('accountId' => $ids), 'customers_info');

        $sql = 'DELETE FROM %table.customers% WHERE customers_id IN (:accountId)';
        $results = ZMRuntime::getDatabase()->updateObj($sql, array('accountId' => $ids), 'customers');
        parent::tearDown();
    }

    /**
     * Test create account.
     */
    public function testCreateAccount()
    {
        $account = $this->createAccount($this->accountData1);
        $accountService = $this->container->get('accountService');
        $account = $accountService->createAccount($account);
        $this->assertNotEqual(0, $account->getId());
        $reloaded = $accountService->getAccountForId($account->getId());
        foreach (array_keys($this->accountData1) as $key) {
            $getter = 'get'.ucwords($key);
            $this->assertEqual($account->$getter(), $reloaded->$getter(), '%s getter='.$getter);
        }
    }

    /**
     * Test create account other.
     */
    public function testCreateAccountOther()
    {
        $account = $this->createAccount($this->accountData1);
        $accountService = $this->container->get('accountService');
        $account = $accountService->createAccount($account);
        $this->assertNotEqual(0, $account->getId());
        $reloaded = $accountService->getAccountForId($account->getId());
        foreach (array_keys($this->accountData2) as $key) {
            $getter = 'get'.ucwords($key);
            $this->assertEqual($account->$getter(), $reloaded->$getter(), '%s getter='.$getter);
        }
    }

    /**
     * Test update account.
     */
    public function testUpdateAccount()
    {
        $account = $this->createAccount($this->accountData1);
        $accountService = $this->container->get('accountService');
        $account = $accountService->createAccount($account);
        $this->assertNotEqual(0, $account->getId());
        $account->setFirstName('foo');
        $accountService->updateAccount($account);
        $reloaded = $accountService->getAccountForId($account->getId());
        foreach (array_keys($this->accountData1) as $key) {
            $getter = 'get'.ucwords($key);
            $this->assertEqual($account->$getter(), $reloaded->$getter(), '%s getter='.$getter);
        }
    }

    /**
     * Test update account other.
     */
    public function testUpdateAccountOther()
    {
        $account = $this->createAccount($this->accountData1);
        $accountService = $this->container->get('accountService');
        $account = $accountService->createAccount($account);
        $this->assertNotEqual(0, $account->getId());
        $account->setSourceId(9999);
        //XXX: can't test this: $account->setSourceOther('other');
        $accountService->updateAccount($account);
        $reloaded = $accountService->getAccountForId($account->getId());
        foreach (array_keys($this->accountData2) as $key) {
            $getter = 'get'.ucwords($key);
            $this->assertEqual($account->$getter(), $reloaded->$getter(), '%s getter='.$getter);
        }
    }

}
