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
            ZMMessages::instance()->error(zm_l10n_get("Sorry, there is no account with the email address '%s'.", $email));
            return $this->findView();
        }

        $newPassword = ZMAuthenticationManager::instance()->mkPassword();
        $newEncrpytedPassword = ZMAuthenticationManager::instance()->encryptPassword($newPassword);

        //TODO: implement...
        ZMSwiftInit::init();
echo 'xx';
echo 'exists: '.class_exists('Swift_Message');
        $message = Swift_Message::newInstance()

            //Give the message a subject
            ->setSubject('Your subject')

            //Set the From address with an associative array
            ->setFrom(array('john@doe.com' => 'John Doe'))

            //Set the To addresses with an associative array
            ->setTo(array('receiver@domain.org', 'other@domain.org' => 'A name'))

            //Give it a body
            ->setBody('Here is the message itself')

            //And optionally an alternative body
            ->addPart('<q>Here is the message itself</q>', 'text/html')
            ;
echo 'yy';
//Create the Transport
$transport = Swift_SmtpTransport::newInstance('localhost', 26)
  ->setUsername('your username')
  ->setPassword('your password')
  ;

/*
You could alternatively use a different transport such as Sendmail or Mail:

//Sendmail
$transport = Swift_SendmailTransport::newInstance('/usr/sbin/sendmail -bs');

//Mail
$transport = Swift_MailTransport::newInstance();
*/

//Create the Mailer using your created Transport
$mailer = Swift_Mailer::newInstance($transport);

//Send the message
$result = $mailer->send($message);

/*
You can alternatively use batchSend() to send the message

$result = $mailer->batchSend($message);
*/




        // report success
        ZMMessages::instance()->success(zm_l10n_get('A new password has been sent to your email address.'));

        return $this->findView('success');
    }

}
