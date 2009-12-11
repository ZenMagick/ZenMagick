<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2009 zenmagick.org
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
 * Admin controller.
 *
 * @author DerManoMann
 * @package org.zenmagick.plugins.masterPassword
 * @version $Id$
 */
class ZMMasterPasswordAdminController extends ZMPluginAdminController {

    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct('master_password_admin', zm_l10n_get('Set Master Password'), 'masterPassword');
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
    public function processPost($request) {
        $masterPassword = $request->getParameter('masterPassword', null);

        // encrypt only if not empty to allow to reset to blank
        if (!empty($masterPassword)) {
            $masterPassword = ZMAuthenticationManager::instance()->getDefaultProvider()->encryptPassword($masterPassword);
        }
        // update
        $this->getPlugin()->set('masterPassword', $masterPassword);
        ZMMessages::instance()->success('Master password updated.');

        return $this->getRedirectView($request);
    }

}

?>
