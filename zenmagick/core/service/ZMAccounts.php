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
 * @package net.radebatz.zenmagick.service
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
        $sql = "select customers_id, customers_gender, customers_firstname, customers_lastname, customers_dob, customers_default_address_id,
                customers_email_address, customers_telephone, customers_fax, customers_email_format, customers_referral, customers_password,
                customers_authorization, customers_newsletter
                from " . TABLE_CUSTOMERS . "
                where customers_id = :accountId";
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
        $sql = "select customers_id, customers_gender, customers_firstname, customers_lastname, customers_dob, customers_default_address_id,
                customers_email_address, customers_telephone, customers_fax, customers_email_format, customers_referral, customers_password,
                customers_authorization, customers_newsletter
                from " . TABLE_CUSTOMERS . "
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
     * @return bool <code>true</code> if the email address exists, <code>false</code> if not.
     */
    function emailExists($email) {
        $db = $this->getDB();
        $sql = "select count(*) as total
                from " . TABLE_CUSTOMERS . "
                where customers_email_address = :email";
        $sql = $db->bindVars($sql, ":email", $email, "string");

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
                 customers_default_address_id, customers_password, customers_authorization, 
                 customers_gender, customers_dob, customers_password, customers_referral
                ) values (:firstName;string, :lastName;string, :email;string, :nickName;string, :phone;string, :fax;string, :newsletterSubscriber;integer,
                  :emailFormat;string, :defaultAddressId;integer, :authorization;integer,
                  :gender;string, :dob;date, :password;string, :referral;string)";
        $sql = $this->bindObject($sql, $account);
        $db->Execute($sql);
        $account->id_ = $db->Insert_ID();
        return $account;
    }

    /**
     * Update an existing account.
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
                    customers_referral = :referral;string
                where customers_id = :accountId";
        $sql = $db->bindVars($sql, ":accountId", $account->getId(), "integer");
        $sql = $this->bindObject($sql, $account);
        $db->Execute($sql);
        return $account;
    }

    /**
     * Get the voucher balance for the given account.
     *
     * @param int accountId The account id.
     * @return float The available balance or <code>0</code>.
     */
    function getVoucherBalanceForId($accountId) {
        $db = $this->getDB();
        $sql = "select amount from " . TABLE_COUPON_GV_CUSTOMER . "
                where customer_id = :accountId";
        $sql = $db->bindVars($sql, ":accountId", $accountId, "integer");

        $results = $db->Execute($sql);
        if (!$results->EOF) {
            return $results->fields['amount'];
        }

        return 0;
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
        $account->globalSubscriber_ = $this->_isGlobalProductSubscriber($account->getId());
        $account->subscribedProducts_ = $this->_getSubscribedProductIds($account->getId());
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
     */
    function _isGlobalProductSubscriber($accountId) {
        $db = $this->getDB();
        $sql = "select global_product_notifications
                from " . TABLE_CUSTOMERS_INFO . "
                where  customers_info_id = :accountId";
        $sql = $db->bindVars($sql, ":accountId", $accountId, "integer");

        $results = $db->Execute($sql);
        return $results->fields['global_product_notifications'] == '1';
    }

    /**
     * Get subscribed product ids.
     */
    function _getSubscribedProductIds($accountId) {
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

}

?>
