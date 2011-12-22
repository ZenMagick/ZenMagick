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

use zenmagick\base\Runtime;

/**
 * Controller for contact us age.
 *
 * @author DerManoMann <mano@zenmagick.org>
 * @package zenmagick.store.sf.mvc.controller
 */
class ZMContactUsController extends ZMController {

    /**
     * {@inheritDoc}
     */
    public function preProcess($request) {
        $request->getToolbox()->crumbtrail->addCrumb($request->getToolbox()->utils->getTitle());
    }

    /**
     * Process a HTTP GET request.
     *
     * @return ZMView A <code>ZMView</code> that handles presentation or <code>null</code>
     * if the controller generates the contents itself.
     */
    public function processGet($request) {
        $contactInfo = $this->getFormData($request);
        if ($request->isRegistered()) {
            $account = $request->getAccount();
            $contactInfo->setName($account->getFullName());
            $contactInfo->setEmail($account->getEmail());

        }
        return $this->findView();
    }

    /**
     * Process a HTTP POST request.
     *
     * @return ZMView A <code>ZMView</code> that handles presentation or <code>null</code>
     * if the controller generates the contents itself.
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
