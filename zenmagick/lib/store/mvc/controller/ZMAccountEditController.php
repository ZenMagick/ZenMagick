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
 * Request controller for account edit page.
 *
 * @author DerManoMann
 * @package org.zenmagick.store.mvc.controller
 * @version $Id: ZMAccountEditController.php 2348 2009-06-29 03:04:18Z dermanomann $
 */
class ZMAccountEditController extends ZMController {

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
    public function handleRequest($request) {
        $request->getToolbox()->crumbtrail->addCrumb("Account", $request->getToolbox()->net->url(FILENAME_ACCOUNT, '', true, false));
        $request->getToolbox()->crumbtrail->addCrumb($request->getToolbox()->utils->getTitle(null, false));
    }

    /**
     * {@inheritDoc}
     */
    public function processGet($request) {
        return $this->findView(null, array('account' => $request->getAccount()));
    }

    /**
     * {@inheritDoc}
     */
    public function processPost($request) {
        $currentAccount = $request->getAccount();
        $account = $this->getFormBean($request)->getAccount();

        if ($account->getEmail() != $currentAccount->getEmail()) {
            // XXX: move into validation rule email changed, so make sure it doesn't exist
            if (ZMAccounts::instance()->emailExists($account->getEmail())) {
                ZMMessages::instance()->error(zm_l10n_get('Sorry, the entered email address already exists.'));
                return $this->findView();
            }
        }

        ZMAccounts::instance()->updateAccount($account);
        ZMMessages::instance()->success(zm_l10n_get('Your account has been updated.'));

        return $this->findView('success');
    }

}

?>
