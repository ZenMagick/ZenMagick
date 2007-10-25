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
 * A gift voucher receiver.
 *
 * <p><strong>NOTE:</strong> The amount is always in the sessions current currency.</p>
 *
 * @author mano
 * @package org.zenmagick.model
 * @version $Id$
 */
class ZMGVReceiver extends ZMModel {
    var $name_;
    var $email_;
    var $amount_;
    var $message_;


    /**
     * Default c'tor.
     */
    function ZMGVReceiver() {
        parent::__construct();

        $this->name_ = '';
        $this->email_ = '';
        $this->amount_ = 0;
        $this->message_ = '';
    }

    /**
     * Default c'tor.
     */
    function __construct() {
        $this->ZMGVReceiver();
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

        $this->name_ = $zm_request->getParameter('to_name', '');
        $this->email_ = $zm_request->getParameter('email', '');
        $this->amount_ = $zm_request->getParameter('amount', 0);
        $this->amount_ = zm_parse_money($this->amount_);
        $this->message_ = $zm_request->getParameter('message', '');
    }


    /**
     * Get the receiver name.
     *
     * @return string The receiver name.
     */
    function getName() { return $this->name_; }

    /**
     * Get the receiver email.
     *
     * @return string The receiver email.
     */
    function getEmail() { return $this->email_; }

    /**
     * Get the amount.
     *
     * @return string The (formatted) amount.
     */
    function getAmount() { return $this->amount_; }

    /**
     * Check if there is a message.
     *
     * @return boolean <code>true</code> if there is a message, <code>false</code> if not.
     */
    function hasMessage() { return !zm_is_empty($this->message_); }

    /**
     * Get the message.
     *
     * @return string The message.
     */
    function getMessage() { return $this->message_; }

    /**
     * Set the receiver name.
     *
     * @param string name The receiver name.
     */
    function setName($name) { $this->name_ = $name; }

    /**
     * Set the receiver email.
     *
     * @param string email The receiver email.
     */
    function setEmail($email) { $this->email_ = $email; }

    /**
     * Set the amount.
     *
     * @param string amount The (formatted) amount.
     */
    function setAmount($amount) { $this->amount_ = $amount; }

    /**
     * Set the message.
     *
     * @param string message The message.
     */
    function setMessage($message) { $this->message_ = $message; }

}

?>
