<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006 ZenMagick
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
 * @package net.radebatz.zenmagick.model
 * @version $Id$
 */
class ZMContactInfo extends ZMModel {
    var $id_;
    var $email_;
    var $recipient_;
    var $message_;


    /**
     * Default c'tor.
     */
    function ZMContactInfo($name='', $email='', $message='') {
        parent::__construct();

		    $this->name_ = $name;
		    $this->email_ = $email;
		    $this->recipient_ = null;
		    $this->message_ = $msg;
    }

    // create new instance
    function __construct($name='', $email='', $message='') {
        $this->ZMContactInfo($name, $email, $message);
    }

    /**
     * Default d'tor.
     */
    function __destruct() {
        parent::__destruct();
    }


    // populate from request
    function populateFromRequest() {
    global $zm_request;
        $this->name_ = $zm_request->getRequestParameter('contactname', '');
        $this->email_ = $zm_request->getRequestParameter('email', '');
        $this->message_ = $zm_request->getRequestParameter('enquiry', '');
    }

    // getter/setter
    function getName() { return $this->name_; }
    function getEmail() { return $this->email_; }
    function getRecipient() { return $this->recipient_; }
    function getMessage() { return $this->message_; }

}

?>
