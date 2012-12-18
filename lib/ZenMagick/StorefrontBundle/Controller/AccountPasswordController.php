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
namespace ZenMagick\StorefrontBundle\Controller;

use ZenMagick\ZenMagickBundle\Controller\DefaultController;

use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Request controller for account password page.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class AccountPasswordController extends DefaultController
{
    /**
     * {@inheritDoc}
     */
    public function processPost($request)
    {
        if (!$this->validate($request, 'account_password')) {
            return $this->findView();
        }

        $account = $this->getUser();
        if (null == $account) {
            $this->container->get('logger')->err('could not access session account');

            return $this->findView('error');
        }

        $oldPassword = $request->request->get('password_current');
        $newPassword = $request->request->get('password_new');
        $confirmPassword = $request->request->get('password_confirmation');

        $encoder = $this->get('security.encoder_factory')->getEncoder($account);
        if (!$encoder->isPasswordValid($account->getPassword(), $oldPassword)) {
            $this->messageService->error(_zm('Your current password did not match the password in our records. Please try again.'));

            return $this->findView();
        }

        // update password
        $newEncodedPassword = $encoder->encodePassword($newPassword);
        $this->container->get('accountService')->setAccountPassword($account->getId(), $newEncodedPassword);

        $this->container->get('event_dispatcher')->dispatch('password_changed', new GenericEvent($this, array('controller' => $this, 'account' => $account, 'clearPassword' => $newPassword)));

        $this->messageService->success(_zm('Your password has been updated.'));

        return $this->findView('success');
    }

}
