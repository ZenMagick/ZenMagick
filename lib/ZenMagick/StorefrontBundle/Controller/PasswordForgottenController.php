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

use ZenMagick\Base\Toolbox;
use ZenMagick\ZenMagickBundle\Controller\DefaultController;
use ZenMagick\StoreBundle\Entity\Account;

use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Request controller for forgotten passwords.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class PasswordForgottenController extends DefaultController
{
    /**
     * {@inheritDoc}
     */
    public function processPost($request)
    {
        $emailAddress = $request->request->get('email_address');
        $account = $this->container->get('accountService')->getAccountForEmailAddress($emailAddress);
        if (null === $account || Account::REGISTERED != $account->getType()) {
            $this->messageService->error(sprintf(_zm("Sorry, there is no account with the email address '%s'."), $emailAddress));

            return $this->findView();
        }

        $encoder = $this->get('security.encoder_factory')->getEncoder($account);
        $minLength = $this->get('settingsService')->get('zenmagick.base.security.authentication.minPasswordLength', 8);
        $newPassword =  Toolbox::random($minLength, Toolbox::RANDOM_MIXED);
        $newEncodedPassword = $encoder->encodePassword($newPassword);

        // update account password (encrypted)
        $this->container->get('accountService')->setAccountPassword($account->getId(), $newEncodedPassword);

        // send email (clear text)
        $settingsService = $this->container->get('settingsService');
        $message = $this->container->get('messageBuilder')->createMessage('password_forgotten', true, $request, array('password' => $newPassword));
        $message->setSubject(sprintf(_zm("Forgotten Password - %s"), $settingsService->get('storeName')))->setTo($emailAddress, $account->getFullName())->setFrom($settingsService->get('storeEmail'));
        $this->container->get('mailer')->send($message);

        $this->container->get('event_dispatcher')->dispatch('password_changed', new GenericEvent($this, array('controller' => $this, 'account' => $account, 'clearPassword' => $newPassword)));

        // report success
        $this->messageService->success(_zm('A new password has been sent to your email address.'));

        return $this->findView('success');
    }

}
