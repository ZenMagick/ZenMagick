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

use zenmagick\base\Runtime;

/**
 * Request controller for unsubscribe page.
 *
 * @author DerManoMann <mano@zenmagick.org>
 * @package zenmagick.store.sf.mvc.controller
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
    function processGet($request) {
        return $this->findView();
    }

    /**
     * {@inheritDoc}
     */
    function processPost($request) {
        if (!Runtime::getSettings()->get('isAllowAnonymousUnsubscribe')) {
            $this->messageService->error(_zm('In order to unsubscribe you need to login first.'));
            return $this->findView();
        }

        if (!$this->validate($request, 'unsubscribe')) {
            return $this->findView();
        }

        $emailAddress = $request->getParameter('email_address');
        $account = ZMAccounts::getAccountForEmailAddress($emailAddress);

        if (null == $account) {
            $this->messageService->error(_zm('Email address not found.'));
        } else {
            if ($account->isNewsletterSubscriber()) {
                // unsubscribe
                $account->setNewsletterSubscriber(false);
                $this->container->get('accountService')->updateAccount($account);
                $this->messageService->success(sprintf(_zm('Email %s unsubscribed.'), $emailAddress));
            } else {
                $this->messageService->warn(_zm('You are already unsubscribed.'));
            }
        }


        return $this->findView();
    }

}
