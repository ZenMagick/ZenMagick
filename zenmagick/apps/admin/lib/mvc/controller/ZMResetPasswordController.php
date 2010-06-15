<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2010 zenmagick.org
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
 * Request controller for forgotten passwords.
 *
 * @author DerManoMann
 * @package org.zenmagick.store.mvc.controller
 * @version $Id$
 */
class ZMResetPasswordController extends ZMController {

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
    public function processPost($request) {
        $email = $request->getParameter('email');
        $user = ZMAdminUsers::instance()->getUserForEmail($email);
        if (null === $user) {
            ZMMessages::instance()->error(sprintf(_zm("Sorry, there is no account with the email address '%s'."), $email));
            return $this->findView();
        }

        $newPassword = ZMAuthenticationManager::instance()->mkPassword();
        $newEncrpytedPassword = ZMAuthenticationManager::instance()->encryptPassword($newPassword);
        $user->setPassword($newEncrpytedPassword);
        ZMAdminUsers::instance()->updateUser($user);

        $mailer = ZMMailer::instance()->getMailer();
        $content = ZMEmails::instance()->createContents('reset_password', false, $request, array('newPassword' => $newPassword));
        $message = ZMMailer::instance()->getMessage('New password...', $content);
        $message->setTo($email);
        $message->setFrom(ZMSettings::get('storeEmail'));
        $result = $mailer->send($message);

        // report success
        ZMMessages::instance()->success(_zm('A new password has been sent to your email address.'));

        return $this->findView('success');
    }

}
