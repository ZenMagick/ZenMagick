<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2009 zenmagick.org
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
 * Accounts.
 *
 * @author DerManoMann
 * @package org.zenmagick.store.services.account
 * @version $Id$
 */
class ZMAccounts extends ZMObject {
    // authorization status constants
    const AUTHORIZATION_ENABLED = 0;
    const AUTHORIZATION_PENDING = 1;
    const AUTHORIZATION_BLOCKED = 4;


    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
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
        return ZMObject::singleton('Accounts');
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
        if (null != ($account = ZMRuntime::getDatabase()->querySingle($sql, $args, array(TABLE_CUSTOMERS, TABLE_CUSTOMERS_INFO), 'Account'))) {
            if (ZMLangUtils::isEmpty($account->getPassword())) {
                $account->setType(ZMZenCartUserSacsHandler::GUEST);
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
        if (null != ($account = ZMRuntime::getDatabase()->querySingle($sql, $args, array(TABLE_CUSTOMERS, TABLE_CUSTOMERS_INFO), 'Account'))) {
            if (ZMLangUtils::isEmpty($account->getPassword())) {
                $account->setType(ZMZenCartUserSacsHandler::GUEST);
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
        foreach (ZMRuntime::getDatabase()->query($sql, $args, array(TABLE_CUSTOMERS, TABLE_CUSTOMERS_INFO), 'Account') as $account) {
            if (ZMLangUtils::isEmpty($account->getPassword())) {
                $account->setType(ZMZenCartUserSacsHandler::GUEST);
            }
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
        return ZMRuntime::getDatabase()->update($sql, $args, TABLE_CUSTOMERS_INFO);
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

        $sql = "INSERT INTO `" . TABLE_CUSTOMERS_INFO . "`
               (customers_info_id, customers_info_date_account_created, global_product_notifications) 
               VALUES (:accountId, now(), :globalProductSubscriber)";
        $args = array('accountId' => $account->getId(), 'globalProductSubscriber' => $account->isGlobalProductSubscriber());
        ZMRuntime::getDatabase()->update($sql, $args, TABLE_CUSTOMERS_INFO);

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

        // check for existence in case record does not exist...
        $sql = "select count(*) as total from " . TABLE_CUSTOMERS_INFO . "
                where customers_info_id = :accountId";
        $result = ZMRuntime::getDatabase()->querySingle($sql, array('accountId' => $account->getId()), array(TABLE_CUSTOMERS_INFO), ZMDatabase::MODEL_RAW);
        if ($result['total'] > 0) {
            $sql = "UPDATE " . TABLE_CUSTOMERS_INFO . "
                    set customers_info_date_account_last_modified = now()
                    where customers_info_id = :accountId";
        } else {
            $sql = "INSERT into " . TABLE_CUSTOMERS_INFO . "(
                    customers_info_id, customers_info_date_account_created, customers_info_date_account_last_modified
                    ) values (:accountId, now(), now())";
        }
        ZMRuntime::getDatabase()->update($sql, array('accountId' => $account->getId()), TABLE_CUSTOMERS_INFO);

        return $account;
    }


    /**
     * Set password for account
     */
    public function setAccountPassword($accountId, $password) {
        $sql = "UPDATE " . TABLE_CUSTOMERS . "
                SET customers_password = :password
                WHERE customers_id = :accountId";
        ZMRuntime::getDatabase()->update($sql, array('accountId' => $accountId, 'password' => $password), TABLE_CUSTOMERS);
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

        return $result['globalProductSubscriber'];
    }

    /**
     * Set the global product subscriber flag
     *
     * @param int accountId The account id.
     * @param boolean globalProductSubscriber <code>true</code> if global product is selected, <code>false</code> if not.
     */
    public function setGlobalProductSubscriber($accountId, $globalProductSubscriber) {
        $sql = "UPDATE " . TABLE_CUSTOMERS_INFO . "
                SET global_product_notifications = :globalProductSubscriber
                WHERE customers_info_id = :accountId";
        $args = array('accountId' => $accountId, 'globalProductSubscriber' => $globalProductSubscriber);
        ZMRuntime::getDatabase()->querySingle($sql, $args, TABLE_CUSTOMERS_INFO);
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
        foreach (Runtime::getDatabase()->query($sql, array('accountId' => $accountId), TABLE_PRODUCTS_NOTIFICATIONS) as $result) {
            $productIds[] = $result['productId'];
        }
        return $productIds;
    }

    /**
     * Set subscribed product ids.
     *
     * @param ZMAccount account The account.
     * @param array productIds The new list of subscribed product ids.
     * @return ZMAccount The aupdated account.
     */
    public function setSubscribedProductIds($account, $productIds) {
        $currentList = $account->getSubscribedProducts();
        $remove = array();
        $add = array();
        foreach ($productIds as $id => $index) {
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
            $sql = "delete from " . TABLE_PRODUCTS_NOTIFICATIONS . "
                    where  customers_id = :accountId
                    and products_id in (:productId)";
            ZMRuntime::getDatabase()->query($sql, array('accountId' => $account->getId(), 'productId' => $remove), TABLE_PRODUCTS_NOTIFICATIONS);
        }

        if (0 < count($add)) {
            $sql = "insert into " . TABLE_PRODUCTS_NOTIFICATIONS . "
                    (products_id, customers_id) values (:productId, :accountId)";
            foreach ($add as $id) {
                ZMRuntime::getDatabase()->query($sql, array('accountId' => $account->getId(), 'productId' => $id), TABLE_PRODUCTS_NOTIFICATIONS);
            }
        }

        $account->setSubscribedProducts($productIds);
        return $account;
    }

}

?>
