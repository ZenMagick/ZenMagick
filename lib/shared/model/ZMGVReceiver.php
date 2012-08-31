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
 * A gift voucher receiver.
 *
 * <p><strong>NOTE:</strong> The amount is always in the sessions current currency.</p>
 *
 * @author DerManoMann
 * @package zenmagick.store.shared.model
 */
class ZMGVReceiver extends ZMObject {
    private $name_;
    private $email_;
    private $amount_;
    private $message_;


    /**
     * Create new instance.
     */
    public function __construct() {
        parent::__construct();
        $this->name_ = null;
        $this->email_ = null;
        $this->amount_ = 0;
        $this->message_ = null;
    }


    /**
     * Get the receiver name.
     *
     * @return string The receiver name.
     */
    public function getName() { return $this->name_; }

    /**
     * Get the receiver email.
     *
     * @return string The receiver email.
     */
    public function getEmail() { return $this->email_; }

    /**
     * Get the amount.
     *
     * @return string The (formatted) amount.
     */
    public function getAmount() { return $this->amount_; }

    /**
     * Check if there is a message.
     *
     * @return boolean <code>true</code> if there is a message, <code>false</code> if not.
     */
    public function hasMessage() { return !empty($this->message_); }

    /**
     * Get the message.
     *
     * @return string The message.
     */
    public function getMessage() { return $this->message_; }

    /**
     * Set the receiver name.
     *
     * @param string name The receiver name.
     */
    public function setName($name) { $this->name_ = $name; }

    /**
     * Set the receiver email.
     *
     * @param string email The receiver email.
     */
    public function setEmail($email) { $this->email_ = $email; }

    /**
     * Set the amount.
     *
     * @param string amount The (formatted) amount.
     */
    public function setAmount($amount) {
        // TODO: this should be passed into the method
        $currencyCode = $this->container->get('request')->getSession()->getCurrencyCode();
        $currency = $this->container->get('currencyService')->getCurrencyForCode($currencyCode);
        $this->amount_ = $currency->parse($amount, false);
    }

    /**
     * Set the message.
     *
     * @param string message The message.
     */
    public function setMessage($message) { $this->message_ = $message; }

}
