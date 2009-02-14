<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2009 ZenMagick
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
 * Request controller for unsubscribe page.
 *
 * @author DerManoMann
 * @package org.zenmagick.rp.uip.controller
 * @version $Id$
 */
class ZMUnsubscribeController extends ZMController {

    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * {@inheritDoc}
     */
    function processGet() {
        return $this->findView();
    }

    /**
     * {@inheritDoc}
     */
    function processPost() {
        if (!ZMSettings::get('isAllowAnonymousUnsubscribe')) {
            ZMMessages::instance()->error(zm_l10n_get('In order to unsubscribe you need to login first.'));
            return $this->findView();
        }

        if (!$this->validate('unsubscribe')) {
            return $this->findView();
        }

        $emailAddress = ZMRequest::getParameter('email_address');
        $account = ZMAccounts::getAccountForEmailAddress($emailAddress);

        if (null == $account) {
            ZMMessages::instance()->error(zm_l10n_get('Email address not found.'));
        } else {
            if ($account->isNewsletterSubscriber()) {
                // unsubscribe
                $account->setNewsletterSubscriber(false);
                ZMAccounts::instance()->updateAccount($account);
                ZMMessages::instance()->success(zm_l10n_get('Email %s unsubscribed.', $emailAddress));
            } else {
                ZMMessages::instance()->warn(zm_l10n_get('You are already unsubscribed.'));
            }
        }


        return $this->findView();
    }

}

?>
