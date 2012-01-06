<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2011 zenmagick.org
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
namespace zenmagick\apps\storefront\controller;

use zenmagick\base\Runtime;
use zenmagick\base\events\Event;

/**
 * Request controller for forgotten passwords.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class PasswordForgottenController extends \ZMController {

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
    public function preProcess($request) {
        $request->getToolbox()->crumbtrail->addCrumb($request->getToolbox()->utils->getTitle());
    }

    /**
     * {@inheritDoc}
     */
    public function processPost($request) {
        $emailAddress = $request->getParameter('email_address');
        $account = $this->container->get('accountService')->getAccountForEmailAddress($emailAddress);
        if (null === $account || \ZMAccount::REGISTERED != $account->getType()) {
            $this->messageService->error(sprintf(_zm("Sorry, there is no account with the email address '%s'."), $emailAddress));
            return $this->findView();
        }

        $authenticationManager = $this->container->get('authenticationManager');
        $newPassword = $authenticationManager->mkPassword();
        $newEncrpytedPassword = $authenticationManager->encryptPassword($newPassword);

        // update account password (encrypted)
        $this->container->get('accountService')->setAccountPassword($account->getId(), $newEncrpytedPassword);

        // send email (clear text)
        $message = $this->container->get('messageBuilder')->createMessage('password_forgotten', true, $request, array('password' => $newPassword));
        $message->setSubject(sprintf(_zm("Forgotten Password - %s"), Runtime::getSettings()->get('storeName')))->setTo($emailAddress, $account->getFullName())->setFrom(Runtime::getSettings()->get('storeEmail'));
        $this->container->get('mailer')->send($message);

        Runtime::getEventDispatcher()->dispatch('password_changed', new Event($this, array('controller' => $this, 'account' => $account, 'clearPassword' => $newPassword)));

        // report success
        $this->messageService->success(_zm('A new password has been sent to your email address.'));

        return $this->findView('success');
    }

}
