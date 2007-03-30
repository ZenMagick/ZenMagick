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
 * Subscriptions.
 * <p>Subscriptions for a particular account.</p
 *
 * @author mano
 * @package net.radebatz.zenmagick.model
 * @version $Id$
 */
class ZMSubscriptions extends ZMModel {
    var $account_;
    var $newsletter_;
    var $productIds_;


    /**
     * Create new subscription info.
     *
     * @param ZMAccount account The account.
     * @param bool newsletter Newsletter subscription flag.
     * @param array productIds List of subscribed product ids.
     */
    function ZMSubscriptions($account, $newsletter=null, $productIds=null) {
        parent::__construct();

        $this->account_ = $account;
        $this->newsletter_ = $newsletter;
        $this->productIds_ = $productIds;
    }

    /**
     * Create new subscription info.
     *
     * @param ZMAccount account The account.
     * @param bool newsletter Newsletter subscription flag.
     * @param array productIds List of subscribed product ids.
     */
    function __construct($account, $newsletter=null, $productIds=null) {
        $this->ZMSubscriptions($account, $newsletter, $productIds);
    }

    /**
     * Default d'tor.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Checks if the user has subscribed to the newsletter.
     *
     * @return bool <code>true</code> if the user is subscribed, <code>false</code> if not.
     */
    function isNewsletterSubscriber() {
    global $zm_accounts;

        if (null == $this->account_) {
            return null === $this->newsletter_ ? false : $this->newsletter_;
        }
        return $zm_accounts->_isNewsletterSubscriber($this->account_);
    }

    /**
     * Checks if the user is a global product subscriber.
     *
     * @return bool <code>true</code> if the user is subscribed, <code>false</code> if not.
     */
    function isGlobalProductSubscriber() {
    global $zm_accounts;

        if (null == $this->account_) {
            return false;
        }
        return $zm_accounts->_isGlobalProductSubscriber($this->account_);
    }

    /**
     * Checks if the user has product subscriptions.
     *
     * @return bool <code>true</code> if the user has product subscriptions, <code>false</code> if not.
     */
    function hasProductSubscriptions() {
    global $zm_accounts;

        if (null == $this->productIds_) {
            $this->productIds_ = $zm_accounts->_getSubscribedProductIds($this->account_);
        }
        return 0 != count($this->productIds_); 
    }

    /**
     * Get the subscribed producst.
     *
     * @return array A list of <code>ZMProduct</code> instances.
     */
    function getSubscribedProductIds() {
    global $zm_accounts;

        if (null == $this->productIds_) {
            $this->productIds_ = $zm_accounts->_getSubscribedProductIds($this->account_);
        }
        return $this->productIds_;
    }

}

?>
