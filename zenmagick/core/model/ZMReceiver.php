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
 * A email receiver.
 *
 * @author mano
 * @package org.zenmagick.model
 * @version $Id$
 */
class ZMReceiver extends ZMModel {
    var $name_;
    var $email_;
    var $message_;


    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();

        $this->name_ = '';
        $this->email_ = '';
        $this->message_ = '';
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
        $this->name_ = ZMRequest::getParameter('to_name', '');
        $this->email_ = ZMRequest::getParameter('email', '');
        $this->message_ = ZMRequest::getParameter('message', '');
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
     * Chkec if there is a message.
     *
     * @return boolean <code>true</code> if there is a message, <code>false</code> if not.
     */
    function hasMessage() { return !empty($this->message_); }

    /**
     * Get the message.
     *
     * @return string The message.
     */
    function getMessage() { return $this->message_; }

}

?>
