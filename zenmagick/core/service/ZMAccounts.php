<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2008 ZenMagick
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
 * @author mano
 * @package org.zenmagick.service
 * @version $Id$
 */
class ZMAccounts extends ZMObject {

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
    function getAccountForId($accountId) {
        $sql = "SELECT c.*, ci.*
                FROM " . TABLE_CUSTOMERS . " c
                  LEFT JOIN " . TABLE_CUSTOMERS_INFO . " ci ON (c.customers_id = ci.customers_info_id)
                WHERE c.customers_id = :id";
        $args = array('id' => $accountId);
        return ZMRuntime::getDatabase()->querySingle($sql, $args, array(TABLE_CUSTOMERS, TABLE_CUSTOMERS_INFO), 'Account');
    }

    /**
     * Get account for the given email address.
     *
     * @param string emailAddress The email address.
     * @return ZMAccount A <code>ZMAccount</code> instance or <code>null</code>.
     */
    function getAccountForEmailAddress($emailAddress) {
        $db = ZMRuntime::getDB();
        $sql = "SELECT c.*, ci.*
                FROM " . TABLE_CUSTOMERS . " c
                  LEFT JOIN " . TABLE_CUSTOMERS_INFO . " ci ON (c.customers_id = ci.customers_info_id)
                WHERE customers_email_address = :email
                AND NOT customers_password = ''";
        $args = array('email' => $emailAddress);
        return ZMRuntime::getDatabase()->querySingle($sql, $args, array(TABLE_CUSTOMERS, TABLE_CUSTOMERS_INFO), 'Account');
    }

    /**
     * Update account login stats.
     *
     * @param int accountId The account id.
     */
    function updateAccountLoginStats($accountId) {
        $sql = "UPDATE " . TABLE_CUSTOMERS_INFO . "
                SET customers_info_date_of_last_logon = now(),
                    customers_info_number_of_logons = customers_info_number_of_logons+1
                WHERE customers_info_id = :id";
        $args = array('id' => $accountId);
        return ZMRuntime::getDatabase()->update($sql, $args, TABLE_CUSTOMERS_INFO);
    }

    /**
     * Checks if a given email address exists.
     *
     * @param string emailAddress The email address.
     * @return boolean <code>true</code> if the email address exists, <code>false</code> if not.
     */
    function emailExists($emailAddress) {
        $sql = "SELECT count(*) as total
                FROM " . TABLE_CUSTOMERS . " c
                WHERE customers_email_address = :email
                AND NOT customers_password = ''";
        $args = array('email' => $emailAddress);
        $result = ZMRuntime::getDatabase()->querySingle($sql, $args, array(TABLE_CUSTOMERS, 'system'));
        return 0 < $result['total'];
    }

    /**
     * Create a new account.
     *
     * @param ZMAccount account The new account.
     * @return ZMAccount The created account incl. the new account id.
     */
    function createAccount($account) {
        $account = ZMRuntime::getDatabase()->createModel(TABLE_CUSTOMERS, $account);

        $sql = "INSERT INTO " . TABLE_CUSTOMERS_INFO . 
               "(customers_info_id, customers_info_date_account_created) 
               VALUES (:id, now())";
        ZMRuntime::getDatabase()->update($sql, array('id' => $account->getId(), TABLE_CUSTOMERS_INFO));

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
    function updateAccount($account) {
        ZMRuntime::getDatabase()->updateModel(TABLE_CUSTOMERS, $account);

        // check for existence in case record does not exist...
        $sql = "select count(*) as total from " . TABLE_CUSTOMERS_INFO ."
                where customers_info_id = :accountId";
        $db = ZMRuntime::getDB();
        $sql = $db->bindVars($sql, ':accountId',  $account->getId(), 'integer');
        $db->Execute($sql);
        $results = $db->Execute($sql);

        if ($results->fields['total'] > 0) {
            $sql = "UPDATE " . TABLE_CUSTOMERS_INFO . "
                    set customers_info_date_account_last_modified = now()
                    where customers_info_id = :accountId";
        } else {
            $sql = "INSERT into " . TABLE_CUSTOMERS_INFO . "(
                    customers_info_id, customers_info_date_account_created, customers_info_date_account_last_modified
                    ) values (:accountId, now(), now())";
        }
        $sql = $db->bindVars($sql, ':accountId',  $account->getId(), 'integer');
        $db->Execute($sql);

        return $account;
    }

    /**
     * Create new account instance.
     */
    function _newAccount($fields) {
        $account = ZMLoader::make("Account");
        $account->id_ = $fields['customers_id'];
        $account->password_ = $fields['customers_password'];
        $account->firstName_ = $fields['customers_firstname'];
        $account->lastName_ = $fields['customers_lastname'];
        $account->nickName_ = $fields['customers_nick'];
        $account->dob_ = $fields['customers_dob'];
        $account->gender_ = $fields['customers_gender'];
        $account->email_ = $fields['customers_email_address'];
        $account->phone_ = $fields['customers_telephone'];
        $account->fax_ = $fields['customers_fax'];
        $account->emailFormat_ = $fields['customers_email_format'];
        $account->referrals_ = $fields['customers_referral'];
        $account->defaultAddressId_ = $fields['customers_default_address_id'];
        $account->authorization_ = $fields['customers_authorization'];
        $account->newsletter_ = 1 == $fields['customers_newsletter'];
        $account->globalSubscriber_ = $this->isGlobalProductSubscriber($account->getId());
        $account->subscribedProducts_ = $this->getSubscribedProductIds($account->getId());
        $account->type_ = ('' != $fields['customers_password'] ? ZM_ACCOUNT_TYPE_REGISTERED : ZM_ACCOUNT_TYPE_GUEST);
        $account->priceGroupId_ = $fields['customers_group_pricing'];

        // custom fields
        foreach (ZMDbUtils::getCustomFields(TABLE_CUSTOMERS) as $field) {
            if (isset($fields[$field[0]])) {
                $account->set($field[0], $fields[$field[0]]);
            }
        }

        return $account;
    }


    /**
     * Set password for account
     */
    function _setAccountPassword($accountId, $password) {
        $db = ZMRuntime::getDB();
        $sql = "UPDATE " . TABLE_CUSTOMERS . "
                SET customers_password = :password
                WHERE customers_id = :accountId";
        $sql = $db->bindVars($sql, ":accountId", $accountId, "integer");
        $sql = $db->bindVars($sql, ":password", $password, "string");
        $results = $db->Execute($sql);
    }


    /**
     * Check for global product subscriber.
     *
     * @param int accountId The account id.
     * @return boolean <code>true</code> if the account is a global product subscriber, <code>false</code> if not.
     */
    function isGlobalProductSubscriber($accountId) {
        $db = ZMRuntime::getDB();
        $sql = "select global_product_notifications
                from " . TABLE_CUSTOMERS_INFO . "
                where  customers_info_id = :accountId";
        $sql = $db->bindVars($sql, ":accountId", $accountId, "integer");

        $results = $db->Execute($sql);

        return $results->fields['global_product_notifications'] == '1';
    }

    /**
     * Set the global product subscriber flag
     *
     * @param int accountId The account id.
     * @param boolean globalProductSubscriber <code>true</code> if global product is selected, <code>false</code> if not.
     */
    function setGlobalProductSubscriber($accountId, $globalProductSubscriber) {
        $db = ZMRuntime::getDB();
        $sql = "update " . TABLE_CUSTOMERS_INFO . "
                set global_product_notifications = :globalProductSubscriber
                where  customers_info_id = :accountId";
        $sql = $db->bindVars($sql, ":accountId", $accountId, "integer");
        $sql = $db->bindVars($sql, ":globalProductSubscriber", $globalProductSubscriber, "integer");

        $results = $db->Execute($sql);
    }

    /**
     * Get subscribed product ids.
     *
     * @param int accountId The account id.
     * @return array A list of subscribed product ids.
     */
    function getSubscribedProductIds($accountId) {
        $db = ZMRuntime::getDB();
        $sql = "select products_id
                from " . TABLE_PRODUCTS_NOTIFICATIONS . "
                where  customers_id = :accountId";
        $sql = $db->bindVars($sql, ":accountId", $accountId, "integer");

        $productIds = array();
        $results = $db->Execute($sql);
        while (!$results->EOF) {
            array_push($productIds, $results->fields['products_id']);
            $results->MoveNext();
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
    function setSubscribedProductIds($account, $productIds) {
        $currentList = array_flip($account->getSubscribedProducts());
        $newList = array_flip($productIds);
        $remove = array();
        $add = array();
        foreach ($newList as $id => $index) {
            if (!array_key_exists($id, $currentList)) {
                $add[] = $id;
            }
        }
        foreach ($currentList as $id => $index) {
            if (!array_key_exists($id, $newList)) {
                $remove[] = $id;
            }
        }

        $db = ZMRuntime::getDB();
        if (0 < count($remove)) {
            $sql = "delete from " . TABLE_PRODUCTS_NOTIFICATIONS . "
                    where  customers_id = :accountId
                    and products_id in (:productIdList)";
            $sql = $db->bindVars($sql, ":accountId", $account->getId(), "integer");
            $sql = ZMDbUtils::bindValueList($sql, ":productIdList", $remove, "integer");
            $results = $db->Execute($sql);
        }

        if (0 < count($add)) {
            foreach ($add as $id) {
                $sql = "insert into " . TABLE_PRODUCTS_NOTIFICATIONS . "
                        (products_id, customers_id) values (:productId, :accountId)";
                $sql = $db->bindVars($sql, ":productId", $id, "integer");
                $sql = $db->bindVars($sql, ":accountId", $account->getId(), "integer");
                $results = $db->Execute($sql);
            }
        }

        $account->setSubscribedProducts($productIds);
        return $account;
    }

}

?>
