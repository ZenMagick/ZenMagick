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
namespace ZenMagick\AdminBundle\Controller;

use ZenMagick\Base\Toolbox;
use ZenMagick\ZenMagickBundle\Controller\DefaultController;

/**
 * Request controller for forgotten passwords.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class ResetPasswordController extends DefaultController
{
    /**
     * {@inheritDoc}
     */
    public function processPost($request)
    {
        $email = $request->request->get('email');
        $adminUserService = $this->container->get('adminUserService');
        $user = $adminUserService->getUserForEmail($email);
        if (null === $user) {
            $this->get('session.flash_bag')->error(sprintf(_zm("Sorry, there is no account with the email address '%s'."), $email));

            return $this->findView();
        }

        $encoder = $this->get('security.encoder_factory')->getEncoder($user);
        $minLength = $this->get('settingsService')->get('zenmagick.base.security.authentication.minPasswordLength', 8);
        $newPassword =  Toolbox::random($minLength, Toolbox::RANDOM_MIXED);
        $newEncrpytedPassword = $encoder->encodePassword($newPassword);
        $user->setPassword($newEncrpytedPassword);
        $adminUserService->updateUser($user);

        $message = $this->container->get('messageBuilder')->createMessage('reset_password', false, $request, array('newPassword' => $newPassword));
        $message->setSubject(_zm('New password request'))->setTo($email)->setFrom($this->container->get('settingsService')->get('storeEmail'));
        $this->container->get('mailer')->send($message);

        // report success
        $this->get('session.flash_bag')->success(_zm('A new password has been sent to your email address.'));

        return $this->findView('success');
    }

}
