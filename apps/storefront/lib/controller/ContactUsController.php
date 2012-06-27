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
namespace zenmagick\apps\store\storefront\controller;

use zenmagick\base\Runtime;

/**
 * Controller for contact us age.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class ContactUsController extends \ZMController {

    /**
     * {@inheritDoc}
     */
    public function processGet($request) {
        $session = $request->getSession();
        $contactInfo = $this->getFormData($request);
        if ($session->isRegistered()) {
            $account = $this->getUser();
            $contactInfo->setName($account->getFullName());
            $contactInfo->setEmail($account->getEmail());

        }
        return $this->findView();
    }

    /**
     * {@inheritDoc}
     */
    public function processPost($request) {
        $contactInfo = $this->getFormData($request);

        // send email
        $message = $this->container->get('messageBuilder')->createMessage('contact_us', true, $request, array('contactInfo' => $contactInfo));
        $message->setSubject(sprintf(_zm("Message from %s"), Runtime::getSettings()->get('storeName')))->setTo($contactInfo->getEmail(), $contactInfo->getName())->setFrom(Runtime::getSettings()->get('storeEmail'));
        $this->container->get('mailer')->send($message);

        $this->messageService->success(_zm('Your message has been successfully sent.'));
        // clear message before displaying form again
        $contactInfo->setMessage('');

        return $this->findView('success');
    }

}
