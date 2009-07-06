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
 * Init plugin to check account authorization.
 *
 * @package org.zenmagick.plugins.init
 * @author DerManoMann
 * @version $Id$
 */
class zm_init_authorization extends Plugin {

    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct('Authorization', 'Check account authorization');
        $this->setScope(Plugin::SCOPE_STORE);
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }

    /**
     * Init this plugin.
     */
    function init() {
        parent::init();

        $account = ZMRequest::getAccount();
        if (null != $account && !ZMSettings::get('isAdmin') && ZMAccounts::AUTHORIZATION_PENDING == $account->getAuthorization()) {
            if (!in_array(ZMRequest::getRequestId(), array(CUSTOMERS_AUTHORIZATION_FILENAME, FILENAME_LOGIN, FILENAME_LOGOFF, FILENAME_CONTACT_US, FILENAME_PRIVACY))) {
                ZMRequest::redirect(ZMToolbox::instance()->net->url(CUSTOMERS_AUTHORIZATION_FILENAME, '', false, false));
            }
        }
    }

}

?>
