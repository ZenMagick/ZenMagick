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

use ZenMagick\Base\ZMObject;

/**
 * Contact info.
 *
 * @author DerManoMann
 * @package zenmagick.store.shared.model
 */
class ZMContactInfo extends ZMObject
{
    private $name;
    private $email;
    private $recipient;
    private $message;

    /**
     * Create new instance.
     *
     * @param string name The contact name.
     * @param string email The contacts email address.
     * @param string message An optional message.
     */
    public function __construct($name=null, $email=null, $message=null)
    {
        parent::__construct();
        $this->name = $name;
        $this->email = $email;
        $this->recipient = null;
        $this->message = $message;
    }

    /**
     * Get the contact name.
     *
     * @return string The contact name.
     */
    public function getName() { return $this->name; }

    /**
     * Set the contact name.
     *
     * @param string name The contact name.
     */
    public function setName($name) { $this->name = $name; }

    /**
     * Get the contact email address.
     *
     * @return string The contact email address.
     */
    public function getEmail() { return $this->email; }

    /**
     * Set the contact email address.
     *
     * @param string email The contact email address.
     */
    public function setEmail($email) { $this->email = $email; }

    /**
     * Get the recipient.
     *
     * @return string The recipient.
     */
    public function getRecipient() { return $this->recipient; }

    /**
     * Set the recipient.
     *
     * @param string recipient The recipient.
     */
    public function setRecipient($recipient) { $this->recipient = $recipient; }

    /**
     * Get the message.
     *
     * @return string The message.
     */
    public function getMessage() { return $this->message; }

    /**
     * Set the message.
     *
     * @param string message The message.
     */
    public function setMessage($message) { $this->message = $message; }

}
