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

namespace ZenMagick\StoreBundle\Services\Account;

use ZMRuntime;
use ZenMagick\Base\Toolbox;
use ZenMagick\Base\ZMObject;
use ZenMagick\Base\Database\Connection;
use ZenMagick\StoreBundle\Entity\Account\Account;

/**
 * Accounts.
 *
 * @author DerManoMann
 */
class Accounts extends ZMObject {
    // authorization status constants
    const AUTHORIZATION_ENABLED = 0;
    const AUTHORIZATION_PENDING = 1;
    const AUTHORIZATION_BLOCKED = 4;

    /**
     * Get account for the given account id.
     *
     * @param int accountId The account id.
     * @return ZenMagick\StoreBundle\Entity\Account\Account A <code>ZenMagick\StoreBundle\Entity\Account\Account</code> instance or <code>null</code>.
     */
    public function getAccountForId($accountId) {
        $sql = "SELECT c.*, ci.*
                FROM %table.customers% c
                  LEFT JOIN %table.customers_info% ci ON (c.customers_id = ci.customers_info_id)
                WHERE c.customers_id = :accountId";
        $args = array('accountId' => $accountId);
        if (null != ($account = ZMRuntime::getDatabase()->querySingle($sql, $args, array('customers', 'customers_info'), 'ZenMagick\StoreBundle\Entity\Account\Account'))) {
            if (Toolbox::isEmpty($account->getPassword())) {
                $account->setType(Account::GUEST);
            }
        }
        return $account;
    }

    /**
     * Get account for the given email address.
     *
     * @param string emailAddress The email address.
     * @return ZenMagick\StoreBundle\Entity\Account\Account A <code>ZenMagick\StoreBundle\Entity\Account\Account</code> instance or <code>null</code>.
     */
    public function getAccountForEmailAddress($emailAddress) {
        $sql = "SELECT c.*, ci.*
                FROM %table.customers% c
                  LEFT JOIN %table.customers_info% ci ON (c.customers_id = ci.customers_info_id)
                WHERE customers_email_address = :email
                AND NOT (customers_password = '')";
        $args = array('email' => $emailAddress);
        if (null != ($account = ZMRuntime::getDatabase()->querySingle($sql, $args, array('customers', 'customers_info'), 'ZenMagick\StoreBundle\Entity\Account\Account'))) {
            if (Toolbox::isEmpty($account->getPassword())) {
                $account->setType(Account::GUEST);
            }
        }
        return $account;
    }

    /**
     * Get all accounts (guest and registered) for the given email address.
     *
     * @param string emailAddress The email address.
     * @return array A <st of code>ZenMagick\StoreBundle\Entity\Account\Account</code> instances.
     */
    public function getAccountsForEmailAddress($emailAddress) {
        $sql = "SELECT c.*, ci.*
                FROM %table.customers% c
                  LEFT JOIN %table.customers_info% ci ON (c.customers_id = ci.customers_info_id)
                WHERE customers_email_address = :email";
        $args = array('email' => $emailAddress);
        $accounts = array();
        foreach (ZMRuntime::getDatabase()->fetchAll($sql, $args, array('customers', 'customers_info'), 'ZenMagick\StoreBundle\Entity\Account\Account') as $account) {
            if (Toolbox::isEmpty($account->getPassword())) {
                $account->setType(Account::GUEST);
            }
            $accounts[] = $account;
        }
        return $accounts;
    }

    /**
     * Get all accounts.
     *
     * @param string type Optional type (<code>Account::REGISTERED<code>, <code>Account::GUEST<code>); default is <code>null</code> for all.
     * @param int limit Optional limit; default is <em>0</em> for all.
     * @return array A <st of code>ZenMagick\StoreBundle\Entity\Account\Account</code> instances.
     */
    public function getAllAccounts($type=null, $limit=0) {
        $sql = "SELECT c.*, ci.*
                FROM %table.customers% c
                  LEFT JOIN %table.customers_info% ci ON (c.customers_id = ci.customers_info_id)";
        if (Account::REGISTERED == $type) {
            $sql .= " WHERE NOT (customers_password = '')";
        } elseif (Account::GUEST == $type) {
            $sql .= " WHERE (customers_password = '')";
        }
        $sql .= " ORDER BY c.customers_id DESC";
        if (0 < $limit) {
            $sql .= " LIMIT ".$limit;
        }

        $accounts = array();
        foreach (ZMRuntime::getDatabase()->fetchAll($sql, array(), array('customers', 'customers_info'), 'ZenMagick\StoreBundle\Entity\Account\Account') as $account) {
            if (Toolbox::isEmpty($account->getPassword())) {
                $account->setType(Account::GUEST);
            }
            $accounts[] = $account;
        }

        return $accounts;
    }

    /**
     * Update account login stats.
     *
     * @param int accountId The account id.
     */
    public function updateAccountLoginStats($accountId) {
        $sql = "UPDATE %table.customers_info%
                SET customers_info_date_of_last_logon = now(),
                    customers_info_number_of_logons = customers_info_number_of_logons+1
                WHERE customers_info_id = :accountId";
        $args = array('accountId' => $accountId);
        return ZMRuntime::getDatabase()->updateObj($sql, $args, 'customers_info');
    }

    /**
     * Checks if a given email address exists.
     *
     * @param string emailAddress The email address.
     * @return boolean <code>true</code> if the email address exists, <code>false</code> if not.
     */
    public function emailExists($emailAddress) {
        $sql = "SELECT count(*) as total
                FROM %table.customers% c
                WHERE customers_email_address = :email
                AND NOT (customers_password = '')";
        $args = array('email' => $emailAddress);
        $result = ZMRuntime::getDatabase()->querySingle($sql, $args, array('customers'), Connection::MODEL_RAW);
        return 0 < $result['total'];
    }

    /**
     * Create a new account.
     *
     * @param ZenMagick\StoreBundle\Entity\Account\Account account The new account.
     * @return ZenMagick\StoreBundle\Entity\Account\Account The created account incl. the new account id.
     */
    public function createAccount($account) {
        $account = ZMRuntime::getDatabase()->createModel('customers', $account);
        $now = new \DateTime();
        $account->setAccountCreateDate($now);
        $account->setLastModifiedDate($now);
        ZMRuntime::getDatabase()->createModel('customers_info', $account);
        return $account;
    }

    /**
     * Update an existing account.
     *
     * <p><strong>NOTE:</strong> This will not update product notification changes!</p>
     * <p>Use <code>setGlobalProductSubscriber(..)</code> and <code>setSubscribedProductIds(..)</code> * to update product subscriptions.</p>
     *
     * @param ZenMagick\StoreBundle\Entity\Account\Account The account.
     * @return ZenMagick\StoreBundle\Entity\Account\Account The updated account.
     */
    public function updateAccount($account) {
        ZMRuntime::getDatabase()->updateModel('customers', $account);
        $now = new \DateTime();
        $account->setLastModifiedDate($now);

        // check for existence in case record does not exist...
        $sql = "SELECT COUNT(*) AS total FROM %table.customers_info%
                WHERE customers_info_id = :accountId";
        $result = ZMRuntime::getDatabase()->querySingle($sql, array('accountId' => $account->getId()), array('customers_info'), Connection::MODEL_RAW);
        if ($result['total'] > 0) {
            ZMRuntime::getDatabase()->updateModel('customers_info', $account);
        } else {
            $account->setAccountCreateDate($now);
            $account = ZMRuntime::getDatabase()->createModel('customers_info', $account);
        }

        return $account;
    }


    /**
     * Set password for account
     */
    public function setAccountPassword($accountId, $password) {
        $sql = "UPDATE %table.customers%
                SET customers_password = :password
                WHERE customers_id = :accountId";
        ZMRuntime::getDatabase()->updateObj($sql, array('accountId' => $accountId, 'password' => $password), 'customers');
    }


    /**
     * Check for global product subscriber.
     *
     * @param int accountId The account id.
     * @return boolean <code>true</code> if the account is a global product subscriber, <code>false</code> if not.
     */
    public function isGlobalProductSubscriber($accountId) {
        $sql = "SELECT global_product_notifications
                FROM %table.customers_info%
                WHERE customers_info_id = :accountId";
        $result = ZMRuntime::getDatabase()->querySingle($sql, array('accountId' => $accountId), 'customers_info');

        return (boolean)$result['globalProductSubscriber'];
    }

    /**
     * Set the global product subscriber flag
     *
     * @param int accountId The account id.
     * @param boolean globalProductSubscriber <code>true</code> if global product is selected, <code>false</code> if not.
     */
    public function setGlobalProductSubscriber($accountId, $globalProductSubscriber) {
        $sql = "UPDATE %table.customers_info%
                SET global_product_notifications = :globalProductSubscriber, customers_info_date_account_last_modified = now()
                WHERE customers_info_id = :accountId";
        $args = array('accountId' => $accountId, 'globalProductSubscriber' => $globalProductSubscriber);
        ZMRuntime::getDatabase()->updateObj($sql, $args, 'customers_info');
    }

    /**
     * Get subscribed product ids.
     *
     * @param int accountId The account id.
     * @return array A list of subscribed product ids.
     */
    public function getSubscribedProductIds($accountId) {
        $sql = "SELECT products_id
                FROM %table.products_notifications%
                WHERE customers_id = :accountId";
        $productIds = array();

        foreach (ZMRuntime::getDatabase()->fetchAll($sql, array('accountId' => $accountId), 'products_notifications') as $result) {
            $productIds[] = $result['productId'];
        }
        return $productIds;
    }

    /**
     * Add subscribed product ids.
     *
     * @param ZenMagick\StoreBundle\Entity\Account\Account account The account.
     * @param arrray productIds A list of product ids to subscribe to.
     * @return ZenMagick\StoreBundle\Entity\Account\Account The updated account.
     */
    public function addSubscribedProductIds($account, $productIds) {
        $sql = "INSERT INTO %table.products_notifications%
                (products_id, customers_id) VALUES (:productId, :accountId)";
        foreach ($productIds as $id) {
            ZMRuntime::getDatabase()->updateObj($sql, array('accountId' => $account->getId(), 'productId' => $id), 'products_notifications');
        }
        $account->addSubscribedProducts($productIds);
        return $account;
    }

    /**
     * Remove subscribed product ids.
     *
     * @param ZenMagick\StoreBundle\Entity\Account\Account account The account.
     * @param arrray productIds A list of product ids to remove subscriptions.
     * @return ZenMagick\StoreBundle\Entity\Account\Account The updated account.
     */
    public function removeSubscribedProductIds($account, $productIds) {
        $sql = "DELETE FROM %table.products_notifications%
                WHERE  customers_id = :accountId
                AND products_id in (:productId)";
        ZMRuntime::getDatabase()->updateObj($sql, array('accountId' => $account->getId(), 'productId' => $productIds), 'products_notifications');
        $account->removeSubscribedProducts($productIds);
        return $account;
    }

    /**
     * Set subscribed product ids.
     *
     * @param ZenMagick\StoreBundle\Entity\Account\Account account The account.
     * @param array productIds The new list of subscribed product ids.
     * @return ZenMagick\StoreBundle\Entity\Account\Account The updated account.
     */
    public function setSubscribedProductIds($account, $productIds) {
        $currentList = $account->getSubscribedProducts();
        $remove = array();
        $add = array();
        foreach ($productIds as $id) {
            if (!in_array($id, $currentList)) {
                $add[] = $id;
            }
        }
        foreach ($currentList as $id) {
            if (!in_array($id, $productIds)) {
                $remove[] = $id;
            }
        }

        if (0 < count($remove)) {
            $sql = "DELETE FROM %table.products_notifications%
                    WHERE  customers_id = :accountId
                    AND products_id in (:productId)";
            ZMRuntime::getDatabase()->updateObj($sql, array('accountId' => $account->getId(), 'productId' => $remove), 'products_notifications');
        }

        if (0 < count($add)) {
            $sql = "INSERT INTO %table.products_notifications%
                    (products_id, customers_id) VALUES (:productId, :accountId)";
            foreach ($add as $id) {
                ZMRuntime::getDatabase()->updateObj($sql, array('accountId' => $account->getId(), 'productId' => $id), 'products_notifications');
            }
        }

        $account->setSubscribedProducts($productIds);
        return $account;
    }

}
