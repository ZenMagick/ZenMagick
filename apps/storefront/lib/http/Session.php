<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2011 zenmagick.org
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
namespace zenmagick\apps\store\storefront\http;

use zenmagick\base\Runtime;
use zenmagick\base\events\Event;

/**
 * Custom session class that adds a number of convenience methods.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class Session extends \zenmagick\http\session\Session {

    /**
     * Create a new instance.
     */
    public function __construct($domain=null, $name=self::DEFAULT_NAME, $secure=false) {
        parent::__construct($domain, $name, $secure);
        if (!Runtime::getSettings()->get('apps.store.storefront.sessions', false)) {
            // fake start to load session data
            $this->data_ = array_merge($_SESSION, $this->data_);
            $this->setName('zenid');
        }
    }


    /**
     * {@inheritDoc}
     * @todo: drop
     */
    public function setValue($name, $value=null, $namespace=null) {
        parent::setValue($name, $value, $namespace);
        if (isset($_SESSION)) {
            $_SESSION[$name] = $value;
        }
    }
    /**
     * {@inheritDoc}
     * @todo: drop
     */
    public function getValue($name, $namespace=null, $default=null) {
        if (null != ($value = parent::getValue($name, $namespace))) {
            return $value;
        }
        if (isset($_SESSION) && array_key_exists($name, $_SESSION)) {
            return $_SESSION[$name];
        }
        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function regenerate() {
        if (Runtime::getSettings()->get('apps.store.storefront.sessions', false)) {
            parent::regenerate();
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getToken($renew=false, $tokenKey=self::SESSION_TOKEN_KEY) {
        // XXX: TODO: remove; hack for zc
        if (Runtime::getSettings()->get('apps.store.storefront.sessions', false)) {
            return parent::getToken($renew);
        } else {
            return parent::getToken($renew, 'securityToken');
        }
    }

    /**
     * Get the current shopping cart.
     *
     * @return mixed The current <strong>zen-cart</strong> shopping cart (may be empty).
     */
    public function getZCShoppingCart() { return $this->getValue('cart'); }

    /**
     * Get the account id.
     *
     * @return int The account id for the currently logged in user or <code>0</code>.
     */
    public function getAccountId() {
        $accountId = $this->getValue('customer_id');
        return null !== $accountId ? $accountId : 0;
    }

    /**
     * Returns the current session type.
     *
     * <p>This type corresponds with the account type.</p>
     *
     * @return char The session type.
     */
    public function getType() {
        $type = $this->getValue('account_type');
        return null === $type ? \ZMAccount::ANONYMOUS : $type;
    }

    /**
     * Returns <code>true</code> if the user is not logged in at all.
     *
     * <p>This is the lowest level of identity.</p>
     *
     * @return boolean <code>true</code> if the current user is anonymous, <code>false</code> if not.
     */
    public function isAnonymous() { return $this->getType() == \ZMAccount::ANONYMOUS; }

    /**
     * Returns <code>true</code> if the user is a guest user.
     *
     * <p>This status level is in the middle between <em>registered</em> and <em>anonymous</em>.</p>
     *
     * @return boolean <code>true</code> if the current user is an guest, <code>false</code> if not.
     */
    public function isGuest() { return $this->getType() == \ZMAccount::GUEST; }

    /**
     * Returns <code>true</code> if the user is a registered user.
     *
     * <p>This is the highest status level.</p>
     *
     * @return boolean <code>true</code> if the current user is registered, <code>false</code> if not.
     */
    public function isRegistered() { return $this->getType() == \ZMAccount::REGISTERED; }

    /**
     * Returns <code>true</code> if the user is logged in.
     *
     * @return boolean <code>true</code> if the current user is logged in, <code>false</code> if not.
     */
    public function isLoggedIn() { return $this->getType() != \ZMAccount::ANONYMOUS; }

    /**
     * Set the account for the current session.
     *
     * @param ZMAccount account The account.
     */
    public function setAccount($account) {
        if (null == $account) {
            $this->setValue('customer_id', '');
        } else {
            $this->setValue('customer_id', $account->getId());
            $this->setValue('customer_default_address_id', $account->getDefaultAddressId());
            $this->setValue('customers_authorization', $account->getAuthorization());
            $this->setValue('customer_first_name', $account->getFirstName());
            $this->setValue('account_type', $account->getType());
            $address = $this->container->get('addressService')->getAddressForId($account->getDefaultAddressId());
            if (null != $address) {
                $this->setValue('customer_country_id', $address->getCountryId());
                $this->setValue('customer_zone_id', $address->getZoneId());
            }
        }
    }

    /**
     * Check if we have a session yet.
     *
     * @return boolean <code>true<code> if the session has been already started.
     */
    public function isStarted() {
        $id = session_id();
        return !empty($id);
    }

    /**
     * Restore the shopping cart contents.
     */
    public function restoreCart() {
        $cart = $this->getValue('cart');
        if (null != $cart) {
            //TODO:
            $cart->restore_contents();
        }
    }

    /**
     * Save the given messages in the session.
     *
     * @param array messages A list of <code>Message</code> objects.
     */
    public function setMessages($messages) {
        $messageToStack = $this->getValue('messageToStack');
        if (!is_array($messageToStack)) {
            $sessionMessages = array();
        }

        foreach ($messages as $msg) {
            array_push($sessionMessages, array('class' => $msg->getRef(), 'text' => $msg->getText(), 'type' => $msg->getType()));
        }

        $this->setValue('messageToStack', $sessionMessages);
    }

    /**
     * Clear all session messages.
     */
    public function clearMessages() {
        $this->setValue('messageToStack', '');
        // just in case
        $this->setValue('messages', '');
        $this->setValue('messages', '', 'zenmagick.http');
    }

    /**
     * Get all session messages.
     *
     * @param array Messages.
     * @deprecated
     */
    public function getMessages() {
        $messages = array();
        $messageToStack = $this->getValue('messageToStack');
        if (is_array($messageToStack)) {
            foreach ($messageToStack as $arr) {
                $message = Runtime::getContainer()->get('ZMMessage');
                $message->setText($arr['text']);
                $message->setType($arr['type']);
                $message->setRef($arr['class']);
                $messages[] = $message;
            }
        }

        return $messages;
    }

    /**
     * Get the client ip address.
     *
     * @return string The client IP address or <code>null</code>.
     */
    public function getClientHostname() {
        return $this->getValue('customers_host_address');
    }

    /**
     * Get currency code.
     *
     * @return string The session currency code or <code>null</code>.
     */
    public function getCurrencyCode() {
        return $this->getValue('currency');
    }

    /**
     * Set currency code.
     *
     * @param string currencyCode The session currency code.
     */
    public function setCurrencyCode($currencyCode) {
        $this->setValue('currency', $currencyCode);
    }

    /**
     * Set the language.
     *
     * @param Language language The language.
     */
    public function setLanguage($language) {
        $this->setValue('language', $language->getDirectory());
        $this->setValue('languages_id', $language->getId());
        $this->setValue('languages_code', $language->getCode());
    }

    /**
     * Get the language.
     *
     * @return Language The language or <code>null</code>.
     */
    public function getLanguage() {
        $languageCode = $this->getValue('languages_code');
        $languageService = $this->container->get('languageService');
        return $languageService->getLanguageForCode($languageCode);
    }

    /**
     * Get the language id.
     *
     * @return int The current language id.
     */
    public function getLanguageId() {
        $languageId = $this->getValue('languages_id');
        return (null !== $languageId ? (int)$languageId : (int)Runtime::getSettings()->get('storeDefaultLanguageId'));
    }

    /**
     * Get the current language code.
     *
     * @return string The language code or <code>null</code>.
     */
    public function getLanguageCode() {
        if (null != ($language = $this->getLanguage())) {
            return $language->getCode();
        }
        return null;
    }

    /**
     * Register an account as user for this session.
     *
     * <p>This operation will fail, for example, if the account is blocked/disabled.</p>
     *
     * @param ZMAccount account The account.
     * @param ZMRequest request The current request.
     * @param mixed source The event source; default is <code>null</code>.
     * @return boolean <code>true</code> if ok, <code>false</code> if not.
     */
    public function registerAccount($account, $request, $source=null) {
        if (\ZMAccounts::AUTHORIZATION_BLOCKED == $account->getAuthorization()) {
            $this->container->get('messageService')->error(_zm('Access denied.'));
            return false;
        }

        // info only
        Runtime::getEventDispatcher()->dispatch('login_success', new Event($this, array('controller' => $this, 'account' => $account, 'request' => $request)));

        // update session with valid account
        $this->setAccount($account);

        // update login stats
        $this->container->get('accountService')->updateAccountLoginStats($account->getId());

        // restore cart contents
        $this->restoreCart();

        return true;
    }

}
