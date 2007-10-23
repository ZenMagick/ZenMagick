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
 * Simple wrapper around <code>$_SESSION</code> to centralise access.
 *
 * @author mano
 * @package net.radebatz.zenmagick
 * @version $Id$
 */
class ZMSession extends ZMObject {

    /**
     * Default c'tor.
     */
    function ZMSession() {
        parent::__construct();

        $this->controller_ = null;
    }

    /**
     * Default c'tor.
     */
    function __construct() {
        $this->ZMSession();
    }

    /**
     * Default d'tor.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Check if the current session is valid.
     *
     * @return boolean <code>true</code> if a valid session exists, <code>false</code> if not.
     */
    function isValid() {
    global $session_started;

        return $session_started;
    }

    /**
     * Create a session value.
     *
     * @param string name The field name.
     * @param mixed value The value.
     */
    function setValue($name, $value) {
        $_SESSION[$name] = $value;
    }

    /**
     * Get a session value.
     *
     * @param string name The field name.
     * @return mixed The value or <code>null</code>.
     */
    function getValue($name) {
        if (isset($_SESSION[$name])) {
            return $_SESSION[$name];
        }

        return null;
    }

    /**
     * Recreate session.
     *
     * @param boolean force If <code>true</code>, force recreation of the session, even if this is disabled.
     */
    function recreate($force=false) {
        if ($force || zm_setting('isSessionRecreate')) {
            zen_session_recreate();
        }
    }

    /**
     * Get the current shopping cart.
     *
     * @return ZMShoppingCart The current shopping cart (may be empty).
     */
    function getShoppingCart() { return isset($_SESSION['cart']) ? $_SESSION['cart'] : null; }

    /**
     * Get the account id.
     *
     * @return int The account id for the currently logged in user or <code>0</code>.
     */
    function getAccountId() { return isset($_SESSION['customer_id']) ? (int)$_SESSION['customer_id'] : 0; }

    /**
     * Returns the current session type.
     *
     * <p>This type corresponds with the account type.</p>
     *
     * @return char The session type.
     */
    function getType() { return array_key_exists('account_type', $_SESSION) ? $_SESSION['account_type'] : ZM_ACCOUNT_TYPE_ANONYMOUS; }

    /**
     * Returns <code>true</code> if the user is not logged in at all.
     *
     * <p>This is the lowest level of identity.</p>
     *
     * @return boolean <code>true</code> if the current user is anonymous, <code>false</code> if not.
     */
    function isAnonymous() { return !array_key_exists('account_type', $_SESSION) || ZM_ACCOUNT_TYPE_ANONYMOUS == $_SESSION['account_type']; }

    /**
     * Returns <code>true</code> if the user is a guest user.
     *
     * <p>This status level is in the middle between <em>registered</em> and <em>anonymous</em>.</p>
     *
     * @return boolean <code>true</code> if the current user is an guest, <code>false</code> if not.
     */
    function isGuest() { return array_key_exists('account_type', $_SESSION) && ZM_ACCOUNT_TYPE_GUEST == $_SESSION['account_type']; }

    /**
     * Returns <code>true</code> if the user is a registered user.
     *
     * <p>This si the highest status level.</p>
     *
     * @return boolean <code>true</code> if the current user is registered, <code>false</code> if not.
     */
    function isRegistered() { return array_key_exists('account_type', $_SESSION) && ZM_ACCOUNT_TYPE_REGISTERED == $_SESSION['account_type']; }

    /**
     * Set the account for the current session.
     *
     * @param ZMAccount account The account.
     */
    function setAccount($account) {
    global $zm_addresses;

        $_SESSION['customer_id'] = $account->getId();
        $_SESSION['customer_default_address_id'] = $account->getDefaultAddressId();
        $_SESSION['customers_authorization'] = $account->getAuthorization();
        $_SESSION['customer_first_name'] = $account->getFirstName();
        $_SESSION['account_type'] = $account->getType();
        $address = $zm_addresses->getAddressForId($account->getDefaultAddressId());
        if (null != $address) {
            $_SESSION['customer_country_id'] = $address->getCountryId();
            $_SESSION['customer_zone_id'] = $address->getZoneId();
        }
    }

    /**
     * Clear the session.
     *
     * <p>This will effectively logoff the curent account.
     */
    function clear() {
        session_destroy();
        unset($_SESSION['account_type']);
        $_SESSION['customers_id'] = '';
    }

    /**
     * Restore the shopping cart contents.
     */
    function restoreCart() {
        $_SESSION['cart']->restore_contents();
    }

    /**
     * Save the given messages in the session.
     *
     * @param array messages A list of <code>ZMMessage</code> objects.
     */
    function setMessages($messages) {
        if (!is_array($_SESSION['messageToStack'])) {
            $sessionMessages = array();
        } else {
            $sessionMessages = $_SESSION['messageToStack'];
        }

        foreach ($messages as $msg) {
            array_push($sessionMessages, array('class' => $msg->getRef(), 'text' => $msg->getText(), 'type' => $msg->getType()));
        }

        $_SESSION['messageToStack'] = $sessionMessages;
    }

    /**
     * Clear all session messages.
     */
    function clearMessages() {
        $_SESSION['messageToStack'] = '';
    }

    /**
     * Get all session messages.
     *
     * @param array Messages.
     */
    function getMessages() {
        $messages = array();
        if (isset($_SESSION['messageToStack']) && is_array($_SESSION['messageToStack'])) {
            foreach ($_SESSION['messageToStack'] as $arr) {
                array_push($messages, $this->create("Message", $arr['text'], $arr['type'], $arr['class']));
            }
        }

        return $messages;
    }

    /**
     * Mark current request for return after login.
     */
    function markRequestAsLoginFollowUp() {
        $_SESSION['navigation']->set_snapshot();
    }

    /**
     * Check if a follow up url exists that should be loaded after a login.
     *
     * @return string The url to go to or <code>null</code>.
     */
    function getLoginFollowUp() {
        $url = null;
        if (sizeof($_SESSION['navigation']->snapshot) > 0) {
            $url = zen_href_link($_SESSION['navigation']->snapshot['page'],
                zen_array_to_string($_SESSION['navigation']->snapshot['get'],
                array(zen_session_name())), $_SESSION['navigation']->snapshot['mode']);
            $_SESSION['navigation']->clear_snapshot();
        }
        return $url;
    }

    /**
     * Get the client ip address.
     *
     * @return string The client IP address or <code>null</code>.
     */
    function getClientAddress() {
        return $_SESSION['REMOTE_ADDR'];
    }

    /**
     * Get the client host name.
     *
     * @return string The client host name or <code>null</code>.
     */
    function getClientHostname() {
        return isset($_SESSION['customers_host_address']) ? $_SESSION['customers_host_address'] : null;
    }

    /**
     * Get currency id.
     *
     * @return int The session currency id.
     */
    function getCurrencyId() {
        return isset($_SESSION['currency']) ? $_SESSION['currency'] : 0;
    }

    /**
     * Set currency id.
     *
     * @param int currencyId The session currency id.
     */
    function setCurrencyId($currencyId) {
        $_SESSION['currency'] = $currencyId;
    }

}

?>
