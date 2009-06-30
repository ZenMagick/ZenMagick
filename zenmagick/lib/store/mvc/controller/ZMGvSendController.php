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
 * Request controller for gv send page.
 *
 * @author DerManoMann
 * @package org.zenmagick.store.mvc.controller
 * @version $Id: ZMGvSendController.php 2350 2009-06-29 04:22:59Z dermanomann $
 */
class ZMGvSendController extends ZMController {

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
    public function handleRequest() {
        ZMCrumbtrail::instance()->addCrumb("Account", ZMToolbox::instance()->net->url(FILENAME_ACCOUNT, '', true, false));
        ZMCrumbtrail::instance()->addCrumb(ZMToolbox::instance()->utils->getTitle(null, false));
    }

    /**
     * {@inheritDoc}
     */
    public function processGet() {
        return $this->findView(null, array('zm_account' => ZMRequest::getAccount()));
    }

    /**
     * {@inheritDoc}
     */
    public function processPost() {
        $gvReceiver = $this->getFormBean();

        // back from confirmation to edit or not valid
        if (null != ZMRequest::getParameter('edit')) {
            return $this->findView();
        }

        $data = array();
        $data['zm_account'] = ZMRequest::getAccount();
        // to fake the email content display
        $data['zm_coupon'] = ZMLoader::make("Coupon", 0, zm_l10n_get('THE_COUPON_CODE'));

        return $this->findView('success', $data);
    }

}

?>
