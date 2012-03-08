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

use zenmagick\base\Runtime;
use zenmagick\base\ZMObject;

/**
 * Accounts.
 *
 * @author DerManoMann
 * @package zenmagick.store.shared.services.account
 */
class ZMAccounts extends ZMObject {
    // authorization status constants
    const AUTHORIZATION_ENABLED = 0;
    const AUTHORIZATION_PENDING = 1;
    const AUTHORIZATION_BLOCKED = 4;


    /**
     * Get instance.
     */
    public static function instance() {
        return Runtime::getContainer()->get('accountService');
    }


    /**
     * Get account for the given account id.
     *
     * @param int accountId The account id.
     * @return ZMAccount A <code>ZMAccount</code> instance or <code>null</code>.
     */
    public function getAccountForId($accountId) {
        $sql = "SELECT c.*, ci.*
                FROM " . TABLE_CUSTOMERS . " c
                  LEFT JOIN " . TABLE_CUSTOMERS_INFO . " ci ON (c.customers_id = ci.customers_info_id)
                WHERE c.customers_id = :accountId";
        $args = array('accountId' => $accountId);
        if (null != ($account = ZMRuntime::getDatabase()->querySingle($sql, $args, array(TABLE_CUSTOMERS, TABLE_CUSTOMERS_INFO), 'ZMAccount'))) {
            if (ZMLangUtils::isEmpty($account->getPassword())) {
                $account->setType(ZMAccount::GUEST);
            }
        }
        return $account;
    }

    /**
     * Get account for the given email address.
     *
     * @param string emailAddress The email address.
     * @return ZMAccount A <code>ZMAccount</code> instance or <code>null</code>.
     */
    public function getAccountForEmailAddress($emailAddress) {
        $sql = "SELECT c.*, ci.*
                FROM " . TABLE_CUSTOMERS . " c
                  LEFT JOIN " . TABLE_CUSTOMERS_INFO . " ci ON (c.customers_id = ci.customers_info_id)
                WHERE customers_email_address = :email
                AND NOT (customers_password = '')";
        $args = array('email' => $emailAddress);
        if (null != ($account = ZMRuntime::getDatabase()->querySingle($sql, $args, array(TABLE_CUSTOMERS, TABLE_CUSTOMERS_INFO), 'ZMAccount'))) {
            if (ZMLangUtils::isEmpty($account->getPassword())) {
                $account->setType(ZMAccount::GUEST);
            }
        }
        return $account;
    }

    /**
     * Get all accounts (guest and registered) for the given email address.
     *
     * @param string emailAddress The email address.
     * @return array A <st of code>ZMAccount</code> instances.
     */
    public function getAccountsForEmailAddress($emailAddress) {
        $sql = "SELECT c.*, ci.*
                FROM " . TABLE_CUSTOMERS . " c
                  LEFT JOIN " . TABLE_CUSTOMERS_INFO . " ci ON (c.customers_id = ci.customers_info_id)
                WHERE customers_email_address = :email";
        $args = array('email' => $emailAddress);
        $accounts = array();
        foreach (ZMRuntime::getDatabase()->fetchAll($sql, $args, array(TABLE_CUSTOMERS, TABLE_CUSTOMERS_INFO), 'ZMAccount') as $account) {
            if (ZMLangUtils::isEmpty($account->getPassword())) {
                $account->setType(ZMAccount::GUEST);
            }
            $accounts[] = $account;
        }
        return $accounts;
    }

    /**
     * Get all accounts.
     *
     * @param string type Optional type (<code>ZMAccount::REGISTERED<code>, <code>ZMAccount::GUEST<code>); default is <code>null</code> for all.
     * @param int limit Optional limit; default is <em>0</em> for all.
     * @return array A <st of code>ZMAccount</code> instances.
     */
    public function getAllAccounts($type=null, $limit=0) {
        $sql = "SELECT c.*, ci.*
                FROM " . TABLE_CUSTOMERS . " c
                  LEFT JOIN " . TABLE_CUSTOMERS_INFO . " ci ON (c.customers_id = ci.customers_info_id)";
        if (ZMAccount::REGISTERED == $type) {
            $sql .= " WHERE NOT (customers_password = '')";
        } else if (ZMAccount::GUEST == $type) {
            $sql .= " WHERE (customers_password = '')";
        }
        $sql .= " ORDER BY c.customers_id DESC";
        if (0 < $limit) {
            $sql .= " LIMIT ".$limit;
        }

        $accounts = array();
        foreach (ZMRuntime::getDatabase()->fetchAll($sql, array(), array(TABLE_CUSTOMERS, TABLE_CUSTOMERS_INFO), 'ZMAccount') as $account) {
            if (ZMLangUtils::isEmpty($account->getPassword())) {
                $account->setType(ZMAccount::GUEST);
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
        $sql = "UPDATE " . TABLE_CUSTOMERS_INFO . "
                SET customers_info_date_of_last_logon = now(),
                    customers_info_number_of_logons = customers_info_number_of_logons+1
                WHERE customers_info_id = :accountId";
        $args = array('accountId' => $accountId);
        return ZMRuntime::getDatabase()->updateObj($sql, $args, TABLE_CUSTOMERS_INFO);
    }

    /**
     * Checks if a given email address exists.
     *
     * @param string emailAddress The email address.
     * @return boolean <code>true</code> if the email address exists, <code>false</code> if not.
     */
    public function emailExists($emailAddress) {
        $sql = "SELECT count(*) as total
                FROM " . TABLE_CUSTOMERS . " c
                WHERE customers_email_address = :email
                AND NOT (customers_password = '')";
        $args = array('email' => $emailAddress);
        $result = ZMRuntime::getDatabase()->querySingle($sql, $args, array(TABLE_CUSTOMERS), ZMDatabase::MODEL_RAW);
        return 0 < $result['total'];
    }

    /**
     * Create a new account.
     *
     * @param ZMAccount account The new account.
     * @return ZMAccount The created account incl. the new account id.
     */
    public function createAccount($account) {
        $account = ZMRuntime::getDatabase()->createModel(TABLE_CUSTOMERS, $account);
        $now = new \DateTime();
        $account->setAccountCreateDate($now);
        $account->setLastModifiedDate($now);
        ZMRuntime::getDatabase()->createModel(TABLE_CUSTOMERS_INFO, $account);
        return $account;
    }

    /**
     * Update an existing account.
     *
     * <p><strong>NOTE:</strong> This will not update product notification
     * changes!</p>
     * <p>Use <code>setGlobalProductSubscriber(..)</code> and
     * <code>setSubscribedProductIds(..)</code> * to update product
     * subscriptions.</p>
     *
     * @param ZMAccount The account.
     * @return ZMAccount The updated account.
     */
    public function updateAccount($account) {
        ZMRuntime::getDatabase()->updateModel(TABLE_CUSTOMERS, $account);
        $now = new \DateTime();
        $account->setLastModifiedDate($now);

        // check for existence in case record does not exist...
        $sql = "SELECT COUNT(*) AS total FROM " . TABLE_CUSTOMERS_INFO . "
                WHERE customers_info_id = :accountId";
        $result = ZMRuntime::getDatabase()->querySingle($sql, array('accountId' => $account->getId()), array(TABLE_CUSTOMERS_INFO), ZMDatabase::MODEL_RAW);
        if ($result['total'] > 0) {
            ZMRuntime::getDatabase()->updateModel(TABLE_CUSTOMERS_INFO, $account);
        } else {
            $account->setAccountCreateDate($now);
            $account = ZMRuntime::getDatabase()->createModel(TABLE_CUSTOMERS_INFO, $account);
        }

        return $account;
    }


    /**
     * Set password for account
     */
    public function setAccountPassword($accountId, $password) {
        $sql = "UPDATE " . TABLE_CUSTOMERS . "
                SET customers_password = :password
                WHERE customers_id = :accountId";
        ZMRuntime::getDatabase()->updateObj($sql, array('accountId' => $accountId, 'password' => $password), TABLE_CUSTOMERS);
    }


    /**
     * Check for global product subscriber.
     *
     * @param int accountId The account id.
     * @return boolean <code>true</code> if the account is a global product subscriber, <code>false</code> if not.
     */
    public function isGlobalProductSubscriber($accountId) {
        $sql = "SELECT global_product_notifications
                FROM " . TABLE_CUSTOMERS_INFO . "
                WHERE customers_info_id = :accountId";
        $result = ZMRuntime::getDatabase()->querySingle($sql, array('accountId' => $accountId), TABLE_CUSTOMERS_INFO);

        return (boolean)$result['globalProductSubscriber'];
    }

    /**
     * Set the global product subscriber flag
     *
     * @param int accountId The account id.
     * @param boolean globalProductSubscriber <code>true</code> if global product is selected, <code>false</code> if not.
     */
    public function setGlobalProductSubscriber($accountId, $globalProductSubscriber) {
        $sql = "UPDATE " . TABLE_CUSTOMERS_INFO . "
                SET global_product_notifications = :globalProductSubscriber, customers_info_date_account_last_modified = now()
                WHERE customers_info_id = :accountId";
        $args = array('accountId' => $accountId, 'globalProductSubscriber' => $globalProductSubscriber);
        ZMRuntime::getDatabase()->updateObj($sql, $args, TABLE_CUSTOMERS_INFO);
    }

    /**
     * Get subscribed product ids.
     *
     * @param int accountId The account id.
     * @return array A list of subscribed product ids.
     */
    public function getSubscribedProductIds($accountId) {
        $sql = "SELECT products_id
                FROM " . TABLE_PRODUCTS_NOTIFICATIONS . "
                WHERE customers_id = :accountId";
        $productIds = array();
        foreach (ZMRuntime::getDatabase()->fetchAll($sql, array('accountId' => $accountId), TABLE_PRODUCTS_NOTIFICATIONS) as $result) {
            $productIds[] = $result['productId'];
        }
        return $productIds;
    }

    /**
     * Set subscribed product ids.
     *
     * @param ZMAccount account The account.
     * @param array productIds The new list of subscribed product ids.
     * @return ZMAccount The updated account.
     */
    public function setSubscribedProductIds($account, $productIds) {
        if (0 == count($productIds)) {
            return $account;
        }

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
            $sql = "DELETE FROM " . TABLE_PRODUCTS_NOTIFICATIONS . "
                    WHERE  customers_id = :accountId
                    AND products_id in (:productId)";
            ZMRuntime::getDatabase()->updateObj($sql, array('accountId' => $account->getId(), 'productId' => $remove), TABLE_PRODUCTS_NOTIFICATIONS);
        }

        if (0 < count($add)) {
            $sql = "INSERT INTO " . TABLE_PRODUCTS_NOTIFICATIONS . "
                    (products_id, customers_id) VALUES (:productId, :accountId)";
            foreach ($add as $id) {
                ZMRuntime::getDatabase()->updateObj($sql, array('accountId' => $account->getId(), 'productId' => $id), TABLE_PRODUCTS_NOTIFICATIONS);
            }
        }

        $account->setSubscribedProducts($productIds);
        return $account;
    }

}
