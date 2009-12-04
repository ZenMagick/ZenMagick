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
 *
 * @version $Id$
 */
?>
<?php


    /**
     * Check for duplicate nickname.
     *
     * @package org.zenmagick.plugins.zm_phpbb2
     * @param array req The request data.
     * @return boolean <code>true</code> if the nickname is valid, <code>false</code> if not.
     */
    function _zmp_is_not_duplicate_nickname($req) {
        // use fresh instance to be sure the phpBB db is selected
        $phpBB = new phpBB();
        return !$phpBB->phpbb_check_for_duplicate_nick($req['nickName']) == 'already_exists';
    }

    /**
     * Check for duplicate email address.
     *
     * @package org.zenmagick.plugins.zm_phpbb2
     * @param array req The request data.
     * @return boolean <code>true</code> if the email is valid, <code>false</code> if not.
     */
    function _zmp_is_not_duplicate_email($req) {
        // use fresh instance to be sure the phpBB db is selected
        $phpBB = new phpBB();
        return !$phpBB->phpbb_check_for_duplicate_email($req['email']) == 'already_exists';
    }

    /**
     * Check for duplicate nickname if changed
     *
     * @package org.zenmagick.plugins.zm_phpbb2
     * @param array req The request data.
     * @return boolean <code>true</code> if the nickname is valid, <code>false</code> if not.
     */
    function _zmp_is_not_duplicate_nickname_changed($req) {
        // the current account
        $account = ZMRequest::instance()->getAccount();

        if ($account->getNickName() != $req['nickName']) {
            // changed
            return _zmp_is_not_duplicate_nickname($req);
        }

        return true;
    }

    /**
     * Check for duplicate email address if changed
     *
     * @package org.zenmagick.plugins.zm_phpbb2
     * @param array req The request data.
     * @return boolean <code>true</code> if the email is valid, <code>false</code> if not.
     */
    function _zmp_is_not_duplicate_email_changed($req) {
        // the current account
        $account = ZMRequest::instance()->getAccount();

        if ($account->getEmail() != $req['email']) {
            // changed
            return _zmp_is_not_duplicate_email($req);
        }

        return true;
    }

?>
