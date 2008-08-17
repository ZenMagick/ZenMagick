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
 * A generic email message container.
 *
 * @author DerManoMann
 * @package org.zenmagick.model
 * @version $Id$
 */
class ZMEmailMessage extends ZMModel {
    var $fromEmail_;
    var $fromName_;
    var $toEmail_;
    var $toName_;
    var $message_;


    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();

        $this->fromEmail_ = null;
        $this->fromName_ = '';
        $this->toEmail_ = null;
        $this->toName_ = '';
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
        $this->fromEmail_ = ZMRequest::getParameter('from_email_address');
        $this->fromName_ = ZMRequest::getParameter('from_name', '');
        $this->toEmail_ = ZMRequest::getParameter('to_email_address');
        $this->toName_ = ZMRequest::getParameter('to_name', '');
        $this->message_ = ZMRequest::getParameter('message', '');
    }

    /**
     * Get the sender email address.
     *
     * @return string The sender email address.
     */
    function getFromEmail() { return $this->fromEmail_; }

    /**
     * Get the sender name.
     *
     * @return string The sender name.
     */
    function getFromName() { return $this->fromName_; }

    /**
     * Get the recipient email address.
     *
     * @return string The recipient email address.
     */
    function getToEmail() { return $this->toEmail_; }

    /**
     * Get the receiver name.
     *
     * @return string The recipient name.
     */
    function getToName() { return $this->toName_; }

    /**
     * Get the message.
     *
     * @return string The message.
     */
    function getMessage() { return $this->message_; }

    /**
     * Set the sender email address.
     *
     * @param string email The sender email address.
     */
    function setFromEmail($email) { $this->fromEmail_ = $email; }

    /**
     * Set the sender name.
     *
     * @param string name The sender name.
     */
    function setFromName($name) { $this->fromName_ = $name; }

    /**
     * Set the recipient email address.
     *
     * @param string email The recipient email address.
     */
    function setToEmail($email) { $this->toEmail_ = $email; }

    /**
     * Set the receiver name.
     *
     * @param string name The recipient name.
     */
    function setToName($name) { $this->toName_ = $name; }

    /**
     * Set the message.
     *
     * @param string message The message.
     */
    function setMessage($message) { $this->message_ = $message; }

    /**
     * Check if there is a message.
     *
     * @return boolean <code>true</code> if there is a message, <code>false</code> if not.
     */
    function hasMessage() { return !empty($this->message_); }

}

?>
