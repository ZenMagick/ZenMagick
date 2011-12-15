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

use zenmagick\base\ZMObject;

/**
 * A generic email message container.
 *
 * @author DerManoMann
 * @package zenmagick.store.shared.model
 */
class ZMEmailMessage extends ZMObject {
    private $fromEmail_;
    private $fromName_;
    private $toEmail_;
    private $toName_;
    private $message_;


    /**
     * Create new instance.
     */
    public function __construct() {
        parent::__construct();
        $this->fromEmail_ = null;
        $this->fromName_ = '';
        $this->toEmail_ = null;
        $this->toName_ = '';
        $this->message_ = '';
    }


    /**
     * Get the sender email address.
     *
     * @return string The sender email address.
     */
    public function getFromEmail() { return $this->fromEmail_; }

    /**
     * Get the sender name.
     *
     * @return string The sender name.
     */
    public function getFromName() { return $this->fromName_; }

    /**
     * Get the recipient email address.
     *
     * @return string The recipient email address.
     */
    public function getToEmail() { return $this->toEmail_; }

    /**
     * Get the receiver name.
     *
     * @return string The recipient name.
     */
    public function getToName() { return $this->toName_; }

    /**
     * Get the message.
     *
     * @return string The message.
     */
    public function getMessage() { return $this->message_; }

    /**
     * Set the sender email address.
     *
     * @param string email The sender email address.
     */
    public function setFromEmail($email) { $this->fromEmail_ = $email; }

    /**
     * Set the sender name.
     *
     * @param string name The sender name.
     */
    public function setFromName($name) { $this->fromName_ = $name; }

    /**
     * Set the recipient email address.
     *
     * @param string email The recipient email address.
     */
    public function setToEmail($email) { $this->toEmail_ = $email; }

    /**
     * Set the receiver name.
     *
     * @param string name The recipient name.
     */
    public function setToName($name) { $this->toName_ = $name; }

    /**
     * Set the message.
     *
     * @param string message The message.
     */
    public function setMessage($message) { $this->message_ = $message; }

    /**
     * Check if there is a message.
     *
     * @return boolean <code>true</code> if there is a message, <code>false</code> if not.
     */
    public function hasMessage() { return !empty($this->message_); }

}
