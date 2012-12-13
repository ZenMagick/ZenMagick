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

namespace ZenMagick\StoreBundle\Entity;

use ZenMagick\Base\Runtime;
use ZenMagick\Base\Toolbox;
use ZenMagick\Base\ZMObject;
use Doctrine\ORM\Mapping as ORM;
use ZenMagick\StoreBundle\Services\Account\Accounts;

use Symfony\Component\Security\Core\User\AdvancedUserInterface;

/**
 * A single user account.
 *
 * @ORM\Table(name="customers",
 *  indexes={
 *      @ORM\Index(name="idx_email_address_zen", columns={"customers_email_address"}),
 *      @ORM\Index(name="idx_referral_zen", columns={"customers_referral"}),
 *      @ORM\Index(name="idx_grp_pricing_zen", columns={"customers_group_pricing"}),
 *      @ORM\Index(name="idx_nick_zen", columns={"customers_nick"}),
 *      @ORM\Index(name="idx_newsletter_zen", columns={"customers_newsletter"}),
 *  })
 * @ORM\Entity(repositoryClass="ZenMagick\StoreBundle\Entity\AccountRepository")
 * @author DerManoMann
 */
class Account extends ZMObject implements AdvancedUserInterface, \Serializable
{
    /** Access level registered. */
    const REGISTERED = 'registered';
    /** Access level guest. */
    const GUEST = 'guest';
    /** Access level anonymous. */
    const ANONYMOUS = 'anonymous';

    /**
     * @var integer $id
     *
     * @ORM\Column(name="customers_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $accountId;

    /**
     * @var string $gender
     *
     * @ORM\Column(name="customers_gender", type="string", length=1, nullable=false)
     */
    private $gender;

    /**
     * @var string $firstName
     *
     * @ORM\Column(name="customers_firstname", type="string", length=32, nullable=false)
     */
    private $firstName;

    /**
     * @var string $lastName
     *
     * @ORM\Column(name="customers_lastname", type="string", length=32, nullable=false)
     */
    private $lastName;

    /**
     * @var \DateTime $dob
     *
     * @ORM\Column(name="customers_dob", type="datetime", nullable=false)
     */
    private $dob;

    /**
     * @var string $email
     *
     * @ORM\Column(name="customers_email_address", type="string", length=96, nullable=false)
     */
    private $email;

    /**
     * @var string $nickName
     *
     * @ORM\Column(name="customers_nick", type="string", length=96, nullable=false)
     */
    private $nickName;

    /**
     * @var integer $defaultAddressId
     *
     * @ORM\Column(name="customers_default_address_id", type="integer", nullable=false)
     */
    private $defaultAddressId;

    /**
     * @var string $phone
     *
     * @ORM\Column(name="customers_telephone", type="string", length=32, nullable=false)
     */
    private $phone;

    /**
     * @var string $fax
     *
     * @ORM\Column(name="customers_fax", type="string", length=32, nullable=true)
     */
    private $fax;

    /**
     * @var string $password
     *
     * @ORM\Column(name="customers_password", type="string", length=40, nullable=false)
     */
    private $password;

    /**
     * @var string $newsletter
     *
     * @ORM\Column(name="customers_newsletter", type="string", length=1, nullable=true)
     */
    private $newsletter;

    /**
     * @var integer $priceGroupId
     *
     * @ORM\Column(name="customers_group_pricing", type="integer", nullable=false)
     */
    private $priceGroupId;

    /**
     * @var string $emailFormat
     *
     * @ORM\Column(name="customers_email_format", type="string", length=4, nullable=false)
     */
    private $emailFormat;

    /**
     * @var integer $authorization
     *
     * @ORM\Column(name="customers_authorization", type="smallint", nullable=false)
     */
    private $authorization;

    /**
     * @var string $referral
     *
     * @ORM\Column(name="customers_referral", type="string", length=32, nullable=false)
     */
    private $referral;

    /**
     * @var string $payPalPayerId
     *
     * @ORM\Column(name="customers_paypal_payerid", type="string", length=20, nullable=false)
     */
    private $payPalPayerId;

    /**
     * @var boolean $payPalEc
     *
     * @ORM\Column(name="customers_paypal_ec", type="boolean", nullable=false)
     */
    private $payPalEc;

    private $globalSubscriber;
    private $subscribedProducts;
    private $salt;
    private $type;

    /**
     * Create new instance.
     */
    public function __construct()
    {
        parent::__construct();
        $this->firstName = '';
        $this->lastName = '';
        $this->dob = null;
        $this->nickName = '';
        $this->gender = '';
        //$this->email = null;
        $this->phone = '';
        $this->fax = null;
        $this->emailFormat = 'TEXT';
        $this->referral = '';
        $this->defaultAddressId = 0;
        $this->password = null;
        $this->authorization = Runtime::getSettings()->get('defaultCustomerApproval');
        $this->newsletter = false;
        $this->salt = null;
        $this->globalSubscriber = false;
        $this->subscribedProducts = null;
        $this->type = self::REGISTERED;
        $this->priceGroupId = 0;
        $this->authorization = Accounts::AUTHORIZATION_ENABLED;
        $this->payPalPayerId = '';
        $this->payPalEc = 0;
    }

    /**
     * Get the account id.
     *
     * @return int The account id.
     */
    public function getAccountId()
    {
        return $this->accountId;
    }

    /**
     * Get the account id.
     *
     * @return int The account id.
     */
    public function getId()
    {
        return $this->accountId;
    }

    /**
     * set the account id.
     *
     * @param int id the account id.
     */
    public function setAccountId($id)
    {
        $this->accountId = $id;

        return $this;
    }

    /**
     * set the account id.
     *
     * @param int id the account id.
     */
    public function setId($id)
    {
        $this->accountId = $id;

        return $this;
    }

    /**
     * Get the first name.
     *
     * @return string The first name.
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * Set the first name.
     *
     * @param string firstName The first name.
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * Get the last name.
     *
     * @return string The last name.
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * Set the last name.
     *
     * @param string lastName The last name.
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * Get the date of birth.
     *
     * @return string The date of birth.
     */
    public function getDob()
    {
        return $this->dob;
    }

    /**
     * Set the date of birth.
     *
     * @param string dob The date of birth.
     */
    public function setDob($dob)
    {
        $this->dob = $dob;

        return $this;
    }

    /**
     * Get the nick name.
     *
     * @return string The nick name.
     */
    public function getNickName()
    {
        return $this->nickName;
    }

    /**
     * Set the nick name.
     *
     * @param string nickName The nick name.
     */
    public function setNickName($nickName)
    {
        $this->nickName = $nickName;

        return $this;
    }

    /**
     * Get the gender.
     *
     * @return string The gender ('f' or 'm').
     */
    public function getGender()
    {
        return $this->gender;
    }

    /**
     * Set the gender.
     *
     * @param string gender The gender ('f' or 'm').
     */
    public function setGender($gender)
    {
        $this->gender = $gender;

        return $this;
    }

    /**
     * Get the email address.
     *
     * @return string The email address.
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set the email address.
     *
     * @parm string email The email address.
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * {@inheritDoc}
     *
     * It is currently represented by an email address.
     */
    public function getUsername()
    {
        return $this->getEmail();
    }

    /**
     * Set the  username (currently the email address)
     *
     * @param string username The username.
     *
     */
    public function setUsername($username)
    {
        $this->setEmail($username);

        return $this;
    }

    /**
     * Get the phone number.
     *
     * @return string The phone number.
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Set the phone number.
     *
     * @param string phone The phone number.
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * Get the fax number.
     *
     * @return string The fax number.
     */
    public function getFax()
    {
        return $this->fax;
    }

    /**
     * Set the fax number.
     *
     * @param string fax The fax number.
     */
    public function setFax($fax)
    {
        $this->fax = $fax;

        return $this;
    }

    /**
     * Set the preferred email format.
     *
     * @param string emailFormat The selected email format.
     */
    public function setEmailFormat($emailFormat)
    {
        $this->emailFormat = $emailFormat;

        return $this;
    }

    /**
     * Get the preferred email format.
     *
     * @return string The selected email format.
     */
    public function getEmailFormat()
    {
        return $this->emailFormat;
    }

    /**
     * Check if the account is set up to receive HTML formatted emails.
     *
     * @return boolean <code>true</code> if HTML is selected as email format, <code>false</code> if not.
     */
    public function isHtmlEmail()
    {
        return 'HTML' == $this->emailFormat;
    }

    /**
     * Check if email notification is disabled.
     *
     * @return boolean <code>true</code> if email notification is disabled, <code>false</code> if not.
     */
    public function isEmailDisabled()
    {
        return 'NONE' == $this->emailFormat || 'OUT' == $this->emailFormat;
    }

    /**
     * Get the referral.
     *
     * @return string The referral.
     */
    public function getReferral()
    {
        return $this->referral;
    }

    /**
     * Set the referral.
     *
     * @param string referral The referral.
     */
    public function setReferral($referral)
    {
        $this->referral = $referral;

        return $this;
    }

    /**
     * Get the default address id (primary address).
     *
     * @return int The primary address id.
     */
    public function getDefaultAddressId()
    {
        return $this->defaultAddressId;
    }

    /**
     * Set the default address id (primary address).
     *
     * @param int addressId The primary address id.
     */
    public function setDefaultAddressId($addressId)
    {
        $this->defaultAddressId = $addressId;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * {@inheritDoc}
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getSalt()
    {
        return $this->salt;
    }
    /**
     * Get authorization.
     *
     * @return string The authorization.
     */
    public function getAuthorization()
    {
        return $this->authorization;
    }

    /**
     * Set authorization.
     *
     * @param string authorization The authorization.
     */
    public function setAuthorization($authorization)
    {
        $this->authorization = $authorization;

        return $this;
    }

    /**
     * Returns <code>true</code> if the account has subscribed to newsletter.
     *
     * @return boolean <code>true</code> if newsletter subsricption ias active, <code>false</code> if not.
     */
    public function isNewsletterSubscriber()
    {
        return Toolbox::asBoolean($this->newsletter);
    }

    /**
     * Set the newsletter subscription status.
     *
     * @param boolean newsletterSubscriber <code>true</code> if newsletter subsricption is selected, <code>false</code> if not.
     */
    public function setNewsletterSubscriber($newsletterSubscriber)
    {
        $this->newsletter = $newsletterSubscriber;

        return $this;
    }

    /**
     * Get the voucher balance.
     *
     * @return float The voucher balance.
     */
    public function getVoucherBalance()
    {
        return Runtime::getContainer()->get('couponService')->getVoucherBalanceForAccountId($this->getId());
    }

    /**
     * Get the formatted full name.
     *
     * @return string The full name.
     */
    public function getFullName()
    {
        return $this->firstName . ' ' . $this->lastName;
    }

    /**
     * Checks if the user is a global product subscriber.
     *
     * @return boolean <code>true</code> if the user is subscribed, <code>false</code> if not.
     */
    public function isGlobalProductSubscriber()
    {
        return $this->globalSubscriber;
    }

    /**
     * Set the global product subscriber status.
     *
     * @param boolean globalProductSubscriber <code>true</code> if global product is selected, <code>false</code> if not.
     */
    public function setGlobalProductSubscriber($globalProductSubscriber)
    {
        $this->globalSubscriber = (boolean) $globalProductSubscriber;

        return $this;
    }

    /**
     * Checks if the user has product subscriptions.
     *
     * @return boolean <code>true</code> if the user has product subscriptions, <code>false</code> if not.
     */
    public function hasProductSubscriptions()
    {
        return 0 != count($this->getSubscribedProducts());
    }

    /**
     * Get the subscribed product ids.
     *
     * @return array A list of product ids.
     */
    public function getSubscribedProducts()
    {
        if (null === $this->subscribedProducts) {
            $this->subscribedProducts = Runtime::getContainer()->get('accountService')->getSubscribedProductIds($this->getId());
        }

        return $this->subscribedProducts;
    }

    /**
     * Set the subscribed product ids.
     *
     * @param array products A list of product ids.
     */
    public function setSubscribedProducts($products)
    {
        $this->subscribedProducts = $products;

        return $this;
    }

    /**
     * Add subscribed product ids.
     *
     * @param array products A list of product ids.
     */
    public function addSubscribedProducts($products)
    {
        $this->subscribedProducts = array_unique(array_merge((array) $this->subscribedProducts, $products));

        return $this;
    }

    /**
     * Remove subscribed product ids.
     *
     * @param array products A list of product ids.
     */
    public function removeSubscribedProducts($products)
    {
        $this->subscribedProducts = array_diff((array) $this->subscribedProducts, $products);

        return $this;
    }

    /**
     * Set the account type.
     *
     * @param char type The account type.
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get the account type.
     *
     * @return char The account type.
     */
    public function getType()
    {
        $roles = $this->getRoles();

        return strtolower(str_replace('ROLE_', '', $roles[0]));
    }

    /**
     * Set the price group id.
     *
     * @param int priceGroupId The price group id.
     */
    public function setPriceGroupId($priceGroupId)
    {
        $this->priceGroupId = $priceGroupId;

        return $this;
    }

    /**
     * Get the price group id.
     *
     * @return int The price group id.
     */
    public function getPriceGroupId()
    {
        return $this->priceGroupId;
    }
    /**
     * Get a price group.
     *
     * @return ZenMagick\StoreBundle\Entity\Account\PriceGroup The group or <code>null</code>.
     */
    public function getPriceGroup()
    {
        return Runtime::getContainer()->get('groupPricingService')->getPriceGroupForId($this->priceGroupId);
    }

    /**
     * {@inheritDoc}
     */
    public function getRoles()
    {
        $roles = array('ROLE_GUEST', 'ROLE_USER');
        if (!empty($this->password)) {
            array_unshift($roles, 'ROLE_REGISTERED');
        }

        return $roles;
    }

    /**
     * {@inheritDoc}
     */
    public function eraseCredentials()
    {
    }

    /**
     * Set newsletter
     *
     * @param  string  $newsletter
     * @return Account
     */
    public function setNewsletter($newsletter)
    {
        $this->newsletter = $newsletter;

        return $this;
    }

    /**
     * Get newsletter
     *
     * @return string
     */
    public function getNewsletter()
    {
        return $this->newsletter;
    }

    /**
     * Set payPalPayerId
     *
     * @param  string  $payPalPayerId
     * @return Account
     */
    public function setPayPalPayerId($payPalPayerId)
    {
        $this->payPalPayerId = $payPalPayerId;

        return $this;
    }

    /**
     * Get payPalPayerId
     *
     * @return string
     */
    public function getPayPalPayerId()
    {
        return $this->payPalPayerId;
    }

    /**
     * Set payPalEc
     *
     * @param  boolean $payPalEc
     * @return Account
     */
    public function setPayPalEc($payPalEc)
    {
        $this->payPalEc = $payPalEc;

        return $this;
    }

    /**
     * Get payPalEc
     *
     * @return boolean
     */
    public function getPayPalEc()
    {
        return $this->payPalEc;
    }

    /**
     * {@inheritDoc}
     */
    public function isAccountNonExpired()
    {
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function isAccountNonLocked()
    {
        return Accounts::AUTHORIZATION_BLOCKED != $this->getAuthorization();
    }

    /**
     * {@inheritDoc}
     */
    public function isCredentialsNonExpired()
    {
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function isEnabled()
    {
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function serialize()
    {
        return serialize(array($this->accountId, $this->salt, $this->password, $this->email));
    }

    /**
     * {@inheritDoc}
     */
    public function unserialize($serialized)
    {
        list($this->accountId, $this->salt, $this->password, $this->email) = unserialize($serialized);
    }

}
