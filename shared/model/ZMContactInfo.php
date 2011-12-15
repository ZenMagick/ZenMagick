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
 * Contact info.
 *
 * @author DerManoMann
 * @package zenmagick.store.shared.model
 */
class ZMContactInfo extends ZMObject {
    private $name_;
    private $email_;
    private $recipient_;
    private $message_;


    /**
     * Create new instance.
     *
     * @param string name The contact name.
     * @param string email The contacts email address.
     * @param string message An optional message.
     */
    public function __construct($name=null, $email=null, $message=null) {
        parent::__construct();
        $this->name_ = $name;
        $this->email_ = $email;
        $this->recipient_ = null;
        $this->message_ = $message;
    }


    /**
     * Get the contact name.
     *
     * @return string The contact name.
     */
    public function getName() { return $this->name_; }

    /**
     * Set the contact name.
     *
     * @param string name The contact name.
     */
    public function setName($name) { $this->name_ = $name; }

    /**
     * Get the contact email address.
     *
     * @return string The contact email address.
     */
    public function getEmail() { return $this->email_; }

    /**
     * Set the contact email address.
     *
     * @param string email The contact email address.
     */
    public function setEmail($email) { $this->email_ = $email; }

    /**
     * Get the recipient.
     *
     * @return string The recipient.
     */
    public function getRecipient() { return $this->recipient_; }

    /**
     * Set the recipient.
     *
     * @param string recipient The recipient.
     */
    public function setRecipient($recipient) { $this->recipient_ = $recipient; }

    /**
     * Get the message.
     *
     * @return string The message.
     */
    public function getMessage() { return $this->message_; }

    /**
     * Set the message.
     *
     * @param string message The message.
     */
    public function setMessage($message) { $this->message_ = $message; }

}
