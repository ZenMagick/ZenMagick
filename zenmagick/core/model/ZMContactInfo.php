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
 * Contact info.
 *
 * @author mano
 * @package org.zenmagick.model
 * @version $Id$
 */
class ZMContactInfo extends ZMModel {
    var $id_;
    var $email_;
    var $recipient_;
    var $message_;


    /**
     * Create new instance.
     *
     * @param string name The contact name.
     * @param string email The contacts email address.
     * @param string message An optional message.
     */
    function ZMContactInfo($name='', $email='', $message='') {
        parent::__construct();

		    $this->name_ = $name;
		    $this->email_ = $email;
		    $this->recipient_ = null;
		    $this->message_ = $msg;
    }

    /**
     * Create new instance.
     *
     * @param string name The contact name.
     * @param string email The contacts email address.
     * @param string message An optional message.
     */
    function __construct($name='', $email='', $message='') {
        $this->ZMContactInfo($name, $email, $message);
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
        $this->name_ = ZMRequest::getParameter('contactname', '');
        $this->email_ = ZMRequest::getParameter('email', '');
        $this->message_ = ZMRequest::getParameter('enquiry', '');
    }

    /**
     * Get the contact name.
     *
     * @return string The contact name.
     */
    function getName() { return $this->name_; }

    /**
     * Get the contact email address.
     *
     * @return string The contact email address.
     */
    function getEmail() { return $this->email_; }

    /**
     * Set the contact name.
     *
     * @param string name The contact name.
     */
    function setName($name) { $this->name_ = $name; }

    /**
     * Set the contact email address.
     *
     * @param string email The contact email address.
     */
    function setEmail($email) { $this->email_ = $email; }

    /**
     * Get the recipient.
     *
     * @return string The recipient.
     */
    function getRecipient() { return $this->recipient_; }

    /**
     * Get the message.
     *
     * @return string The message.
     */
    function getMessage() { return $this->message_; }

}

?>
