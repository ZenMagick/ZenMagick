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
 * A single user account.
 *
 * @author mano
 * @package org.zenmagick.model.account
 * @version $Id$
 */
class ZMAccount extends ZMModel {
    var $id_;
    var $firstName_;
    var $lastName_;
    var $dob_;
    var $nickName_;
    var $gender_;
    var $email_;
    var $phone_;
    var $fax_;
    var $emailFormat_;
    var $referral_;
    var $defaultAddressId_;
    var $password_;
    var $authorization_;
    var $newsletter_;
    var $globalSubscriber_;
    var $subscribedProducts_;
    var $type_;
    var $priceGroupId_;


    /**
     * Create new instance.
     */
    function ZMAccount() {
        parent::__construct();

        $this->id_ = 0;
        $this->firstName_ = '';
        $this->lastName_ = '';
        $this->dob_ = '';
        $this->nickName_ = '';
        $this->gender_ = '';
        $this->email_ = '';
        $this->phone_ = '';
        $this->fax_ = '';
        $this->emailFormat_ = 'TEXT';
        $this->referrals_ = '';
        $this->defaultAddressId_ = 0;
        $this->password_ = '';
        $this->authorization_ = 0;
        $this->newsletter_ = false;
        $this->globalSubscriber_ = false;
        $this->subscribedProducts_ = array();
        $this->type_ = ZM_ACCOUNT_TYPE_REGISTERED;
        $this->priceGroupId_ = 0;
    }

    /**
     * Create new instance.
     */
    function __construct() {
        $this->ZMAccount();
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Populate all available fields from the given request.
     *
     * @param array req A request; if <code>null</code>, use the current <code>ZMRequest</code> instead.
     */
    function populate($req=null) {
    global $zm_request;

        $this->firstName_ = $zm_request->getParameter('firstname', '');
        $this->lastName_ = $zm_request->getParameter('lastname', '');
        $this->dob_ = $zm_request->getParameter('dob', '01/01/1970');
        $this->nickName_ = $zm_request->getParameter('nick', '');
        $this->gender_ = $zm_request->getParameter('gender', '');
        $this->email_ = trim($zm_request->getParameter('email_address', ''));
        $this->phone_ = $zm_request->getParameter('telephone', '');
        $this->fax_ = $zm_request->getParameter('fax', '');
        $this->emailFormat_ = $zm_request->getParameter('email_format', 'TEXT');
        $this->referral_ = $zm_request->getParameter('referral', '');
        $this->newsletter_ = $zm_request->getParameter('newsletter', false);
    }


    /**
     * Get the account id.
     *
     * @return int The account id.
     */
    function getId() { return $this->id_; }

    /**
     * Set the account id.
     *
     * @param int id The account id.
     */
    function setId($id) { $this->id_ = $id; }

    /**
     * Get the first name.
     *
     * @return string The first name.
     */
    function getFirstName() { return $this->firstName_; }

    /**
     * Set the first name.
     *
     * @param string firstName The first name.
     */
    function setFirstName($firstName) { $this->firstName_ = $firstName; }

    /**
     * Get the last name.
     *
     * @return string The last name.
     */
    function getLastName() { return $this->lastName_; }

    /**
     * Set the last name.
     *
     * @param string lastName The last name.
     */
    function setLastName($lastName) { $this->lastName_ = $lastName; }

    /**
     * Get the date of birth.
     *
     * @return string The date of birth.
     */
    function getDob() { return $this->dob_; }

    /**
     * Set the date of birth.
     *
     * @param string dob The date of birth.
     */
    function setDob($dob) { $this->dob_ = $dob; }

    /**
     * Get the nick name.
     *
     * @return string The nick name.
     */
    function getNickName() { return $this->nickName_; }

    /**
     * Set the nick name.
     *
     * @param string nickName The nick name.
     */
    function setNickName($nickName) { $this->nickName_ = $nickName; }

    /**
     * Get the gender.
     *
     * @return string The gender ('f' or 'm').
     */
    function getGender() { return $this->gender_; }

    /**
     * Set the gender.
     *
     * @param string gender The gender ('f' or 'm').
     */
    function setGender($gender) { $this->gender_ = $gender; }

    /**
     * Get the email address.
     *
     * @return string The email address.
     */
    function getEmail() { return $this->email_; }

    /**
     * Set the email address.
     *
     * @parm string email The email address.
     */
    function setEmail($email) { $this->email_ = $email; }

    /**
     * Get the phone number.
     *
     * @return string The phone number.
     */
    function getPhone() { return $this->phone_; }

    /**
     * Set the phone number.
     *
     * @param string phone The phone number.
     */
    function setPhone($phone) { $this->phone_ = $phone; }

    /**
     * Get the fax number.
     *
     * @return string The fax number.
     */
    function getFax() { return $this->fax_; }

    /**
     * Set the fax number.
     *
     * @param string fax The fax number.
     */
    function setFax($fax) { $this->fax_ = $fax; }

    /**
     * Set the preferred email format.
     *
     * @param string emailFormat The selected email format.
     */
    function setEmailFormat($emailFormat) { $this->emailFormat_ = $emailFormat; }

    /**
     * Get the preferred email format.
     *
     * @return string The selected email format.
     */
    function getEmailFormat() { return $this->emailFormat_; }

    /**
     * Check if the account is set up to receive HTML formatted emails.
     *
     * @return boolean <code>true</code> if HTML is selected as email format, <code>false</code> if not.
     */
    function isHtmlEmail() { return 'HTML' == $this->emailFormat_; }

    /**
     * Check if email notification is disabled.
     *
     * @return boolean <code>true</code> if email notification is disabled, <code>false</code> if not.
     */
    function isEmailDisabled() { return 'NONE' == $this->emailFormat_ || 'OUT' == $this->emailFormat_; }

    /**
     * Get the referral.
     *
     * @return string The referral.
     */
    function getReferral() { return $this->referral_; }

    /**
     * Set the referral.
     *
     * @param string referral The referral.
     */
    function setReferral($referral) { $this->referral_ = $referral; }

    /**
     * Get the default address id (primary address).
     *
     * @return int The primary address id.
     */
    function getDefaultAddressId() { return $this->defaultAddressId_; }

    /**
     * Set the default address id (primary address).
     *
     * @param int addressId The primary address id.
     */
    function setDefaultAddressId($addressId) { $this->defaultAddressId_ = $addressId; }

    /**
     * Get the password.
     *
     * @return string The password.
     */
    function getPassword() { return $this->password_; }

    /**
     * Set the password.
     *
     * @param string password The (encrypted) password.
     */
    function setPassword($password) { $this->password_ = $password; }

    /**
     * Get authorization.
     *
     * @return string The authorization.
     */
    function getAuthorization() { return $this->authorization_; }

    /**
     * Set authorization.
     *
     * @param string authorization The authorization.
     */
    function setAuthorization($authorization) { $this->authorization_ = $authorization; }

    /**
     * Returns <code>true</code> if the account has subscribed to newsletter.
     *
     * @return boolean <code>true</code> if newsletter subsricption ias active, <code>false</code> if not.
     */
    function isNewsletterSubscriber() { return $this->newsletter_; }

    /**
     * Set the newsletter subscription status.
     *
     * @param boolean newsletterSubscriber <code>true</code> if newsletter subsricption is selected, <code>false</code> if not.
     */
    function setNewsletterSubscriber($newsletterSubscriber) { $this->newsletter_ = $newsletterSubscriber; }

    /**
     * Get the voucher balance.
     *
     * @return float The voucher balance.
     */
    function getVoucherBalance() {
    global $zm_coupons;

        return $zm_coupons->getVoucherBalanceForAccountId($this->id_);
    }

    /**
     * Get the formatted full name.
     *
     * @return string The full name.
     */
    function getFullName() { return $this->firstName_ . ' ' . $this->lastName_; }

    /**
     * Checks if the user is a global product subscriber.
     *
     * @return boolean <code>true</code> if the user is subscribed, <code>false</code> if not.
     */
    function isGlobalProductSubscriber() { 
        return $this->globalSubscriber_;
    }

    /**
     * Set the global product subscriber status.
     *
     * @param boolean globalProductSubscriber <code>true</code> if global product is selected, <code>false</code> if not.
     */
    function setGlobalProductSubscriber($globalProductSubscriber) { $this->globalSubscriber_ = $globalProductSubscriber; }

    /**
     * Checks if the user has product subscriptions.
     *
     * @return boolean <code>true</code> if the user has product subscriptions, <code>false</code> if not.
     */
    function hasProductSubscriptions() {
        return 0 != count($this->subscribedProducts_); 
    }

    /**
     * Get the subscribed product ids.
     *
     * @return array A list of product ids.
     */
    function getSubscribedProducts() {
        return $this->subscribedProducts_;
    }

    /**
     * Set the subscribed product ids.
     *
     * @param array products A list of product ids.
     */
    function setSubscribedProducts($products) {
        $this->subscribedProducts_ = $products;
    }

    /**
     * Set the account type.
     *
     * @param char type The account type.
     */
    function setType($type) {
        $this->type_ = $type;
    }

    /**
     * Get the account type.
     *
     * @return char The account type.
     */
    function getType() {
        return $this->type_;
    }

    /**
     * Set the price group id.
     *
     * @param int priceGroupId The price group id.
     */
    function setPriceGroupId($priceGroupId) {
        $this->priceGroupId_ = $priceGroupId;
    }

    /**
     * Get the price group id.
     *
     * @return int The price group id.
     */
    function getPriceGroupId() {
        return $this->priceGroupId_;
    }
    /**
     * Get a price group.
     *
     * @return ZMPriceGroup The group or <code>null</code>.
     */
    function getPriceGroup() {
    global $zm_groupPricing;

        if (!isset($zm_groupPricing)) {
            $zm_groupPricing = $this->create("GroupPricing");
        }

        return $zm_groupPricing->getPriceGroupForId($this->priceGroupId_);
    }

}

?>
