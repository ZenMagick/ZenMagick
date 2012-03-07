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
namespace zenmagick\apps\store\admin\controller;

use zenmagick\base\Runtime;

/**
 * Request controller for forgotten passwords.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class ResetPasswordController extends \ZMController {

    /**
     * {@inheritDoc}
     */
    public function processPost($request) {
        $email = $request->getParameter('email');
        $adminUserService = $this->container->get('adminUserService');
        $user = $adminUserService->getUserForEmail($email);
        if (null === $user) {
            $this->messageService->error(sprintf(_zm("Sorry, there is no account with the email address '%s'."), $email));
            return $this->findView();
        }

        $authenticationManager = $this->container->get('authenticationManager');
        $newPassword = $authenticationManager->mkPassword();
        $newEncrpytedPassword = $authenticationManager->encryptPassword($newPassword);
        $user->setPassword($newEncrpytedPassword);
        $adminUserService->updateUser($user);

        $message = $this->container->get('messageBuilder')->createMessage('reset_password', false, $request, array('newPassword' => $newPassword));
        $message->setSubject(_zm('New password request'))->setTo($email)->setFrom(Runtime::getSettings()->get('storeEmail'));
        $this->container->get('mailer')->send($message);

        // report success
        $this->messageService->success(_zm('A new password has been sent to your email address.'));

        return $this->findView('success');
    }

}
