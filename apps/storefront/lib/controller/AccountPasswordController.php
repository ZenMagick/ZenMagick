<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2012 zenmagick.org
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
namespace zenmagick\apps\store\storefront\controller;

use zenmagick\base\Runtime;
use zenmagick\base\events\Event;

/**
 * Request controller for account password page.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class AccountPasswordController extends \ZMController {

    /**
     * {@inheritDoc}
     */
    public function processPost($request) {
        if (!$this->validate($request, 'account_password')) {
            return $this->findView();
        }

        $account = $request->getAccount();
        if (null == $account) {
            Runtime::getLogging()->error('could not access session account');
            return $this->findView('error');
        }

        $oldPassword = $request->request->get('password_current');
        $newPassword = $request->request->get('password_new');
        $confirmPassword = $request->request->get('password_confirmation');

        $authenticationManager = $this->container->get('authenticationManager');
        if (!$authenticationManager->validatePassword($oldPassword, $account->getPassword())) {
            $this->messageService->error(_zm('Your current password did not match the password in our records. Please try again.'));
            return $this->findView();
        }

        // update password
        $newEncrpytedPassword = $authenticationManager->encryptPassword($newPassword);
        $this->container->get('accountService')->setAccountPassword($account->getId(), $newEncrpytedPassword);

        Runtime::getEventDispatcher()->dispatch('password_changed', new Event($this, array('controller' => $this, 'account' => $account, 'clearPassword' => $newPassword)));

        $this->messageService->success(_zm('Your password has been updated.'));

        return $this->findView('success');
    }

}
