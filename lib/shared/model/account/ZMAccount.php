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

use zenmagick\base\Runtime;
use zenmagick\base\Toolbox;
use zenmagick\base\ZMObject;

/**
 * A single user account.
 *
 * @author DerManoMann
 * @package zenmagick.store.shared.model.account
 */
class ZMAccount extends ZMObject {
    /** Access level registered. */
    const REGISTERED = 'registered';
    /** Access level guest. */
    const GUEST = 'guest';
    /** Access level anonymous. */
    const ANONYMOUS = 'anonymous';

    private $firstName_;
    private $lastName_;
    private $dob_;
    private $nickName_;
    private $gender_;
    private $email_;
    private $phone_;
    private $fax_;
    private $emailFormat_;
    private $referral_;
    private $defaultAddressId_;
    private $password_;
    private $authorization_;
    private $newsletter_;
    private $globalSubscriber_;
    private $subscribedProducts_;
    private $type_;
    private $priceGroupId_;


    /**
     * Create new instance.
     */
    public function __construct() {
        parent::__construct();

        $this->setId(0);
        $this->firstName_ = '';
        $this->lastName_ = '';
        $this->dob_ = null;
        $this->nickName_ = '';
        $this->gender_ = '';
        $this->email_ = null;
        $this->phone_ = '';
        $this->fax_ = null;
        $this->emailFormat_ = 'TEXT';
        $this->referral_ = '';
        $this->defaultAddressId_ = 0;
        $this->password_ = null;
        $this->authorization_ = Runtime::getSettings()->get('defaultCustomerApproval');
        $this->newsletter_ = false;
        $this->globalSubscriber_ = false;
        $this->subscribedProducts_ = null;
        $this->type_ = self::REGISTERED;
        $this->priceGroupId_ = 0;
        $this->authorization_ = ZMAccounts::AUTHORIZATION_ENABLED;
    }


    /**
     * Get the account id.
     *
     * @return int The account id.
     */
    public function getId() { return $this->get('accountId'); }

    /**
     * Set the account id.
     *
     * @param int id The account id.
     */
    public function setId($id) { $this->set('accountId', $id); }

    /**
     * Get the first name.
     *
     * @return string The first name.
     */
    public function getFirstName() { return $this->firstName_; }

    /**
     * Set the first name.
     *
     * @param string firstName The first name.
     */
    public function setFirstName($firstName) { $this->firstName_ = $firstName; }

    /**
     * Get the last name.
     *
     * @return string The last name.
     */
    public function getLastName() { return $this->lastName_; }

    /**
     * Set the last name.
     *
     * @param string lastName The last name.
     */
    public function setLastName($lastName) { $this->lastName_ = $lastName; }

    /**
     * Get the date of birth.
     *
     * @return string The date of birth.
     */
    public function getDob() { return $this->dob_; }

    /**
     * Set the date of birth.
     *
     * @param string dob The date of birth.
     */
    public function setDob($dob) { $this->dob_ = $dob; }

    /**
     * Get the nick name.
     *
     * @return string The nick name.
     */
    public function getNickName() { return $this->nickName_; }

    /**
     * Set the nick name.
     *
     * @param string nickName The nick name.
     */
    public function setNickName($nickName) { $this->nickName_ = $nickName; }

    /**
     * Get the gender.
     *
     * @return string The gender ('f' or 'm').
     */
    public function getGender() { return $this->gender_; }

    /**
     * Set the gender.
     *
     * @param string gender The gender ('f' or 'm').
     */
    public function setGender($gender) { $this->gender_ = $gender; }

    /**
     * Get the email address.
     *
     * @return string The email address.
     */
    public function getEmail() { return $this->email_; }

    /**
     * Set the email address.
     *
     * @parm string email The email address.
     */
    public function setEmail($email) { $this->email_ = $email; }

    /**
     * Get the phone number.
     *
     * @return string The phone number.
     */
    public function getPhone() { return $this->phone_; }

    /**
     * Set the phone number.
     *
     * @param string phone The phone number.
     */
    public function setPhone($phone) { $this->phone_ = $phone; }

    /**
     * Get the fax number.
     *
     * @return string The fax number.
     */
    public function getFax() { return $this->fax_; }

    /**
     * Set the fax number.
     *
     * @param string fax The fax number.
     */
    public function setFax($fax) { $this->fax_ = $fax; }

    /**
     * Set the preferred email format.
     *
     * @param string emailFormat The selected email format.
     */
    public function setEmailFormat($emailFormat) { $this->emailFormat_ = $emailFormat; }

    /**
     * Get the preferred email format.
     *
     * @return string The selected email format.
     */
    public function getEmailFormat() { return $this->emailFormat_; }

    /**
     * Check if the account is set up to receive HTML formatted emails.
     *
     * @return boolean <code>true</code> if HTML is selected as email format, <code>false</code> if not.
     */
    public function isHtmlEmail() { return 'HTML' == $this->emailFormat_; }

    /**
     * Check if email notification is disabled.
     *
     * @return boolean <code>true</code> if email notification is disabled, <code>false</code> if not.
     */
    public function isEmailDisabled() { return 'NONE' == $this->emailFormat_ || 'OUT' == $this->emailFormat_; }

    /**
     * Get the referral.
     *
     * @return string The referral.
     */
    public function getReferral() { return $this->referral_; }

    /**
     * Set the referral.
     *
     * @param string referral The referral.
     */
    public function setReferral($referral) { $this->referral_ = $referral; }

    /**
     * Get the default address id (primary address).
     *
     * @return int The primary address id.
     */
    public function getDefaultAddressId() { return $this->defaultAddressId_; }

    /**
     * Set the default address id (primary address).
     *
     * @param int addressId The primary address id.
     */
    public function setDefaultAddressId($addressId) { $this->defaultAddressId_ = $addressId; }

    /**
     * Get the password.
     *
     * @return string The password.
     */
    public function getPassword() { return $this->password_; }

    /**
     * Set the password.
     *
     * @param string password The (encrypted) password.
     */
    public function setPassword($password) { $this->password_ = $password; }

    /**
     * Get authorization.
     *
     * @return string The authorization.
     */
    public function getAuthorization() { return $this->authorization_; }

    /**
     * Set authorization.
     *
     * @param string authorization The authorization.
     */
    public function setAuthorization($authorization) { $this->authorization_ = $authorization; }

    /**
     * Returns <code>true</code> if the account has subscribed to newsletter.
     *
     * @return boolean <code>true</code> if newsletter subsricption ias active, <code>false</code> if not.
     */
    public function isNewsletterSubscriber() { return Toolbox::asBoolean($this->newsletter_); }

    /**
     * Set the newsletter subscription status.
     *
     * @param boolean newsletterSubscriber <code>true</code> if newsletter subsricption is selected, <code>false</code> if not.
     */
    public function setNewsletterSubscriber($newsletterSubscriber) { $this->newsletter_ = $newsletterSubscriber; }

    /**
     * Get the voucher balance.
     *
     * @return float The voucher balance.
     */
    public function getVoucherBalance() {
        return $this->container->get('couponService')->getVoucherBalanceForAccountId($this->getId());
    }

    /**
     * Get the formatted full name.
     *
     * @return string The full name.
     */
    public function getFullName() { return $this->firstName_ . ' ' . $this->lastName_; }

    /**
     * Checks if the user is a global product subscriber.
     *
     * @return boolean <code>true</code> if the user is subscribed, <code>false</code> if not.
     */
    public function isGlobalProductSubscriber() {
        return $this->globalSubscriber_;
    }

    /**
     * Set the global product subscriber status.
     *
     * @param boolean globalProductSubscriber <code>true</code> if global product is selected, <code>false</code> if not.
     */
    public function setGlobalProductSubscriber($globalProductSubscriber) { $this->globalSubscriber_ = (boolean)$globalProductSubscriber; }

    /**
     * Checks if the user has product subscriptions.
     *
     * @return boolean <code>true</code> if the user has product subscriptions, <code>false</code> if not.
     */
    public function hasProductSubscriptions() {
        return 0 != count($this->getSubscribedProducts());
    }

    /**
     * Get the subscribed product ids.
     *
     * @return array A list of product ids.
     */
    public function getSubscribedProducts() {
        if (null === $this->subscribedProducts_) {
            $this->subscribedProducts_ = $this->container->get('accountService')->getSubscribedProductIds($this->getId());
        }
        return $this->subscribedProducts_;
    }

    /**
     * Set the subscribed product ids.
     *
     * @param array products A list of product ids.
     */
    public function setSubscribedProducts($products) {
        $this->subscribedProducts_ = $products;
    }

    /**
     * Set the account type.
     *
     * @param char type The account type.
     */
    public function setType($type) {
        $this->type_ = $type;
    }

    /**
     * Get the account type.
     *
     * @return char The account type.
     */
    public function getType() {
        return $this->type_;
    }

    /**
     * Check if this account is currently logged in (guest/registered).
     *
     * <p>Effectively, this is the same as doing: <code>$account->getType() != ZMAccount::ANONYMOUS</code>.</p>
     *
     * @return boolean <code>true</code> if, and only if this account is not anonymous.
     */
    public function isLoggedIn() {
        return self::ANONYMOUS != $this->type_;
    }

    /**
     * Set the price group id.
     *
     * @param int priceGroupId The price group id.
     */
    public function setPriceGroupId($priceGroupId) {
        $this->priceGroupId_ = $priceGroupId;
    }

    /**
     * Get the price group id.
     *
     * @return int The price group id.
     */
    public function getPriceGroupId() {
        return $this->priceGroupId_;
    }
    /**
     * Get a price group.
     *
     * @return ZMPriceGroup The group or <code>null</code>.
     */
    public function getPriceGroup() {
        return $this->container->get('groupPricingService')->getPriceGroupForId($this->priceGroupId_);
    }

}
