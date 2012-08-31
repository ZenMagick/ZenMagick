<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2012 zenmagick.org
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
namespace ZenMagick\apps\storefront\controller;

use ZenMagick\base\events\Event;

/**
 * Request controller for account edit page.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class AccountEditController extends \ZMController {

    /**
     * {@inheritDoc}
     */
    public function processGet($request) {
        return $this->findView(null, array('account' => $this->getUser()));
    }

    /**
     * {@inheritDoc}
     */
    public function processPost($request) {
        $currentAccount = $this->getUser();
        $account = $this->getFormData($request)->getAccount();

        if ($account->getEmail() != $currentAccount->getEmail()) {
            // XXX: move into validation rule email changed, so make sure it doesn't exist
            if ($this->container->get('accountService')->emailExists($account->getEmail())) {
                $this->messageService->error(_zm('Sorry, the entered email address already exists.'));
                return $this->findView();
            }
        }

        $this->container->get('accountService')->updateAccount($account);
        $this->messageService->success(_zm('Your account has been updated.'));

        $args = array('request' => $request, 'controller' => $this, 'account' => $account);
        $this->container->get('event_dispatcher')->dispatch('account_updated', new Event($this, $args));

        return $this->findView('success');
    }

}
