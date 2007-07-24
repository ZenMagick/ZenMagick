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
 * A single user account.
 *
 * @author mano
 * @package net.radebatz.zenmagick.model.account
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


    /**
     * Default c'tor.
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
        $this->type_ = ZM_ACCOUNT_TYPE_REGULAR;
    }

    /**
     * Default c'tor.
     */
    function __construct() {
        $this->ZMAccount();
    }

    /**
     * Default d'tor.
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


    // validate this account
    function isValid() {
    global $zm_messages, $zm_accounts;
        $msgCount = count($zm_messages->getMessages());

        if ($this->gender_ != 'm' && $this->gender_ != 'f') {
            $zm_messages->error(zm_l10n_get("Please choose a title."));
        }

        if (strlen($this->firstName_) < zm_setting('firstNameMinLength')) {
            $zm_messages->error(zm_l10n_get("Your First Name must contain a minimum of %s characters.", zm_setting('firstNameMinLength')));
        }

        if (strlen($this->lastName_) < zm_setting('lastNameMinLength')) {
            $zm_messages->error(zm_l10n_get("Your Last Name must contain a minimum of %s characters.", zm_setting('lastNameMinLength')));
        }

        if (!zm_checkdate($this->dob_)) {
            $zm_messages->error(zm_l10n_get("Your Date of Birth must be in this format: DD/MM/YYYY (eg 21/05/1970)"));
        }

        if (!zm_valid_email($this->email_)) {
            $zm_messages->error(zm_l10n_get("Your E-Mail Address does not appear to be valid - please make any necessary corrections."));
        } else if($zm_accounts->emailExists($this->email_)) {
            $zm_messages->error(zm_l10n_get("Your E-Mail Address already exists in our database."));
        }

        if (strlen($this->phone_) < zm_setting('phoneMinLength')) {
            $zm_messages->error(zm_l10n_get("Your Telephone Number must contain a minimum of %s characters.", zm_setting('phoneMinLength')));
        }
        if (strlen($this->password_) < zm_setting('passwordMinLength')) {
            $zm_messages->error(zm_l10n_get("Your Password must contain a minimum of %s characters.", zm_setting('passwordMinLength')));
        }

        return count($zm_messages->getMessages()) == $msgCount;
    }


    /**
     * Get the account id.
     *
     * @return int The account id.
     */
    function getId() { return $this->id_; }

    /**
     * Get the first name.
     *
     * @return string The first name.
     */
    function getFirstName() { return $this->firstName_; }

    /**
     * Get the last name.
     *
     * @return string The last name.
     */
    function getLastName() { return $this->lastName_; }

    /**
     * Get the date of birth.
     *
     * @return string The date of birth.
     */
    function getDob() { return $this->dob_; }

    /**
     * Get the nick name.
     *
     * @return string The nick name.
     */
    function getNickName() { return $this->nickname_; }

    /**
     * Get the gender.
     *
     * @return string The gender ('f' or 'm').
     */
    function getGender() { return $this->gender_; }

    /**
     * Get the email address.
     *
     * @return string The email address.
     */
    function getEmail() { return $this->email_; }

    /**
     * Get the phone number.
     *
     * @return string The phone number.
     */
    function getPhone() { return $this->phone_; }

    /**
     * Get the fax number.
     *
     * @return string The fax number.
     */
    function getFax() { return $this->fax_; }

    /**
     * Get the preferred email format.
     *
     * @return string The selected email format.
     */
    function getEmailFormat() { return $this->emailFormat_; }

    /**
     * Check if the account is set up to receive HTML formatted emails.
     *
     * @return bool <code>true</code> if HTML is selected as email format, <code>false</code> if not.
     */
    function isHtmlEmail() { return 'HTML' == $this->emailFormat_; }

    /**
     * Check if email notification is disabled.
     *
     * @return bool <code>true</code> if email notification is disabled, <code>false</code> if not.
     */
    function isEmailDisabled() { return 'NONE' == $this->emailFormat_ || 'OUT' == $this->emailFormat_; }

    /**
     * Get the referral.
     *
     * @return string The referral.
     */
    function getReferral() { return $this->referral_; }

    /**
     * Get the default address id (primary address).
     *
     * @return int The primary address id.
     */
    function getDefaultAddressId() { return $this->defaultAddressId_; }

    /**
     * Get the password.
     *
     * @return string The password.
     */
    function getPassword() { return $this->password_; }

    /**
     * Get the authorization.
     *
     * @return string The authorization.
     */
    function getAuthorization() { return $this->authorization_; }

    /**
     * Returns <code>true</code> if the account has subscribed to newsletter.
     *
     * @return bool <code>true</code> if newsletter subsricption ias active, <code>false</code> if not.
     */
    function isNewsletterSubscriber() { return $this->newsletter_; }

    /**
     * Get the voucher balance.
     *
     * @return float The voucher balance.
     */
    function getVoucherBalance() {
    global $zm_accounts;
        return $zm_accounts->getVoucherBalanceForId($this->id_);
    }
    function getFullName() { return $this->firstName_ . ' ' . $this->lastName_; }

    /**
     * Checks if the user is a global product subscriber.
     *
     * @return bool <code>true</code> if the user is subscribed, <code>false</code> if not.
     */
    function isGlobalProductSubscriber() { 
        return $this->globalSubscriber_;
    }

    /**
     * Checks if the user has product subscriptions.
     *
     * @return bool <code>true</code> if the user has product subscriptions, <code>false</code> if not.
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
     * Get the account type.
     *
     * @return char The account type.
     */
    function getType() {
        return $this->type_;
    }

    /**
     * Set the default address id (primary address).
     *
     * @param int addressId The primary address id.
     */
    function setDefaultAddressId($addressId) { $this->defaultAddressId_ = $addressId; }

}

?>
