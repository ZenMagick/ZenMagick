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
namespace ZenMagick\apps\storefront\Http;

use ZenMagick\Base\Runtime;
use ZenMagick\Base\Events\Event;

/**
 * Custom session class that adds a number of convenience methods.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class Session extends \ZenMagick\Http\Session\Session {

    /**
     * {@inheritDoc}
     * @todo: drop
     */
    public function set($name, $value=null) {
        // ZCSMELL
        if (!$this->isStarted()) $this->start();
        parent::set($name, $value);
        if (isset($_SESSION)) {
            $_SESSION[$name] = $value;
        }
    }
    /**
     * {@inheritDoc}
     * @todo: drop
     */
    public function get($name, $default=null) {
        if (null != ($value = parent::get($name, $default))) {
            return $value;
        }
        if (isset($_SESSION) && array_key_exists($name, $_SESSION)) {
            return $_SESSION[$name];
        }
        return $default;
    }

    /**
     * Get the account id.
     *
     * @return int The account id for the currently logged in user or <code>0</code>.
     */
    public function getAccountId() {
        $accountId = $this->get('customer_id');
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
        $type = $this->get('account_type');
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
            $this->set('customer_id', '');
        } else {
            $this->set('customer_id', $account->getId());
            $this->set('customer_default_address_id', $account->getDefaultAddressId());
            $this->set('customers_authorization', $account->getAuthorization());
            $this->set('customer_first_name', $account->getFirstName());
            $this->set('account_type', $account->getType());
            $address = $this->container->get('addressService')->getAddressForId($account->getDefaultAddressId());
            if (null != $address) {
                $this->set('customer_country_id', $address->getCountryId());
                $this->set('customer_zone_id', $address->getZoneId());
            }
        }
    }

    /**
     * Restore the shopping cart contents.
     */
    public function restoreCart() {
        $cart = $this->get('cart');
        if (null != $cart) {
            //TODO:
            $cart->restore_contents();
        }
    }

    /**
     * Set the language.
     *
     * @param Language language The language.
     */
    public function setLanguage($language) {
        $this->set('language', $language->getDirectory());
        $this->set('languages_id', $language->getId());
        $this->set('languages_code', $language->getCode());
    }

    /**
     * Get the language.
     *
     * @return Language The language or <code>null</code>.
     */
    public function getLanguage() {
        $languageCode = $this->get('languages_code');
        $languageService = $this->container->get('languageService');
        return $languageService->getLanguageForCode($languageCode);
    }

    /**
     * Get the language id.
     *
     * @return int The current language id.
     */
    public function getLanguageId() {
        $languageId = $this->get('languages_id');
        return (null !== $languageId ? (int)$languageId : (int)Runtime::getSettings()->get('storeDefaultLanguageId'));
    }

    /**
     * Get the currency code.
     *
     * @return string The current currency code.
     */
    public function getCurrencyCode() {
        return $this->get('currency');
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
     * @param ZenMagick\Http\Request request The current request.
     * @param mixed source The event source; default is <code>null</code>.
     * @return boolean <code>true</code> if ok, <code>false</code> if not.
     */
    public function registerAccount($account, $request, $source=null) {
        if (\ZMAccounts::AUTHORIZATION_BLOCKED == $account->getAuthorization()) {
            $this->getFlashBag()->error(_zm('Access denied.'));
            return false;
        }

        // info only
        $this->container->get('event_dispatcher')->dispatch('login_success', new Event($this, array('controller' => $this, 'account' => $account, 'request' => $request)));

        // update session with valid account
        $this->setAccount($account);

        // update login stats
        $this->container->get('accountService')->updateAccountLoginStats($account->getId());

        // restore cart contents
        $this->container->get('shoppingCart')->setAccountId($account->getId());
        $this->restoreCart();

        return true;
    }

}
