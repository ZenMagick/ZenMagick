<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006,2007 ZenMagick
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
class ZMAccounts extends ZMService {

    /**
     * Default c'tor.
     */
    function ZMAccounts() {
        parent::__construct();
    }

    /**
     * Default c'tor.
     */
    function __construct() {
        $this->ZMAccounts();
    }

    /**
     * Default d'tor.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Get account for the given account id.
     *
     * @param int accountId The account id.
     * @return ZMAccount A <code>ZMAccount</code> instance or <code>null</code>.
     */
    function &getAccountForId($accountId) {
        $db = $this->getDB();
        $sql = "select c.customers_id, c.customers_gender, c.customers_firstname, c.customers_lastname, c.customers_dob, c.customers_default_address_id,
                c.customers_email_address, c.customers_telephone, c.customers_fax, c.customers_email_format, c.customers_referral, c.customers_password,
                c.customers_authorization, c.customers_newsletter, c.customers_nick, c.customers_group_pricing
                from " . TABLE_CUSTOMERS . " c
                where c.customers_id = :accountId";
        $sql = $db->bindVars($sql, ":accountId", $accountId, "integer");
        $results = $db->Execute($sql);
        $account = null;
        if (0 < $results->RecordCount()) {
            $account = $this->_newAccount($results->fields);
        }
        return $account;
    }

    /**
     * Get account for the given email address.
     *
     * @param string emailAddress The email address.
     * @return ZMAccount A <code>ZMAccount</code> instance or <code>null</code>.
     */
    function &getAccountForEmailAddress($emailAddress) {
        $db = $this->getDB();
        $sql = "select c.customers_id, c.customers_gender, c.customers_firstname, c.customers_lastname, c.customers_dob, c.customers_default_address_id,
                c.customers_email_address, c.customers_telephone, c.customers_fax, c.customers_email_format, c.customers_referral, c.customers_password,
                c.customers_authorization, c.customers_newsletter, c.customers_nick, c.customers_group_pricing
                from " . TABLE_CUSTOMERS . " c
                where customers_email_address = :emailAddress";
        $sql = $db->bindVars($sql, ":emailAddress", $emailAddress, "string");
        $results = $db->Execute($sql);
        $account = null;
        if (0 < $results->RecordCount()) {
            $account = $this->_newAccount($results->fields);
        }
        return $account;
    }

    /**
     * Update account login stats.
     *
     * @param int accountId The account id.
     */
    function updateAccountLoginStats($accountId) {
        $db = $this->getDB();
        $sql = "UPDATE " . TABLE_CUSTOMERS_INFO . "
                SET customers_info_date_of_last_logon = now(),
                    customers_info_number_of_logons = customers_info_number_of_logons+1
                WHERE customers_info_id = :accountId";
        $sql = $db->bindVars($sql, ':accountId',  $accountId, 'integer');
        $db->Execute($sql);
    }

    /**
     * Checks if a given email address exists.
     *
     * @param string email The email address.
     * @return boolean <code>true</code> if the email address exists, <code>false</code> if not.
     */
    function emailExists($email) {
        $db = $this->getDB();
        $sql = "select count(*) as total
                from " . TABLE_CUSTOMERS . " c
                where customers_email_address = :email
                and NOT customers_password = :emptyPassword";
        $sql = $db->bindVars($sql, ":email", $email, "string");
        $sql = $db->bindVars($sql, ":emptyPassword", '', "string");

        $results = $db->Execute($sql);
        return $results->fields['total'] > 0;
    }

    /**
     * Create a new account.
     *
     * @param ZMAccount The new account.
     * @return ZMAccount The created account incl. the new account id.
     */
    function &createAccount(&$account) {
        $db = $this->getDB();
        $sql = "insert into " . TABLE_CUSTOMERS . "(
                 customers_firstname, customers_lastname, customers_email_address, customers_nick, 
                 customers_telephone, customers_fax, customers_newsletter, customers_email_format, 
                 customers_default_address_id, customers_authorization, 
                 customers_gender, customers_dob, customers_password, customers_referral, customers_group_pricing
                ) values (:firstName;string, :lastName;string, :email;string, :nickName;string,
                  :phone;string, :fax;string, :newsletterSubscriber;integer, :emailFormat;string,
                  :defaultAddressId;integer, :authorization;integer,
                  :gender;string, :dob;date, :password;string, :referral;string, :groupId;integer)";
        $sql = $this->bindObject($sql, $account);
        $db->Execute($sql);
        $account->id_ = $db->Insert_ID();

        $sql = "INSERT into " . TABLE_CUSTOMERS_INFO . "(
                customers_info_id, customers_info_date_account_created
                ) values (:accountId, now())";
        $sql = $db->bindVars($sql, ':accountId',  $account->getId(), 'integer');
        $db->Execute($sql);

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
    function &updateAccount(&$account) {
        $db = $this->getDB();
        $sql = "update " . TABLE_CUSTOMERS . " set
                customers_firstname = :firstName;string,
                customers_lastname = :lastName;string,
                customers_email_address = :email;string,
                customers_nick = :nickName;string, 
                customers_telephone = :phone;string,
                customers_fax = :fax;string,
                customers_newsletter = :newsletterSubscriber;integer,
                customers_email_format = :emailFormat;string, 
                customers_default_address_id = :defaultAddressId;integer,
                customers_password = :password;string,
                customers_authorization = :authorization;integer, 
                customers_gender = :gender;string,
                customers_dob = :dob;date,
                customers_referral = :referral;string,
                customers_group_pricing = :groupId;integer
                where customers_id = :accountId";
        $sql = $db->bindVars($sql, ":accountId", $account->getId(), "integer");
        $sql = $this->bindObject($sql, $account);
        $db->Execute($sql);

        // check for existence in case record does not exist...
        $sql = "select count(*) as total from " . TABLE_CUSTOMERS_INFO ."
                where customers_info_id = :accountId";
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
    function &_newAccount($fields) {
        $account = $this->create("Account");
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
        $account->groupId_ = $fields['customers_group_pricing'];
        return $account;
    }


    /**
     * Set password for account
     */
    function _setAccountPassword($accountId, $password) {
        $db = $this->getDB();
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
        $db = $this->getDB();
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
        $db = $this->getDB();
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
        $db = $this->getDB();
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
    function &setSubscribedProductIds(&$account, $productIds) {
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

        $db = $this->getDB();
        if (0 < count($remove)) {
            $sql = "delete from " . TABLE_PRODUCTS_NOTIFICATIONS . "
                    where  customers_id = :accountId
                    and products_id in (:productIdList)";
            $sql = $db->bindVars($sql, ":accountId", $account->getId(), "integer");
            $sql = $this->bindValueList($sql, ":productIdList", $remove, "integer");
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
