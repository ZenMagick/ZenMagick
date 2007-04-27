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
     * @return bool <code>true</code> if a valid session exists, <code>false</code> if not.
     */
    function isValid() {
    global $session_started;

        return $session_started;
    }

    /**
     * Recreate session.
     *
     * @param bool force If <code>true</code>, force recreation of the session, even if this is disabled.
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
     * Returns <code>true</code> if the user is not logged in.
     *
     * @return bool <code>true</code> if the current user is guest, <code>false</code> if not.
     */
    function isGuest() { return !array_key_exists('customer_id', $_SESSION) || '' == $_SESSION['customer_id']; }

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
        $address = $zm_addresses->getAddressForId($account->getDefaultAddressId());
        $_SESSION['customer_country_id'] = $address->getCountry();
        $_SESSION['customer_zone_id'] = $address->getZoneId();
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

}

?>
