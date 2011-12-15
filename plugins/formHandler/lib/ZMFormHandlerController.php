<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2011 zenmagick.org
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

use zenmagick\base\ZMObject;

/**
 * Generic form handler controller.
 *
 * @author DerManoMann <mano@zenmagick.org>
 * @package org.zenmagick.plugins.formHandler
 */
class ZMFormHandlerController extends ZMController {

    /**
     * Create the form data object.
     *
     * @param ZMRequest request The current request.
     * @return ZMObject The model.
     */
    protected function createFormData($request) {
        $data = new ZMObject();
        foreach ($request->getParameterMap() as $name => $value) {
            $data->set($name, $value);
        }

        return $data;
    }

    /**
     * {@inheritDoc}
     */
    public function processGet($request) {
        return $this->findView(null, array('formData' => $this->createFormData($request)));
    }

    /**
     * {@inheritDoc}
     */
    public function processPost($request) {
        // create model
        $data = array('formData' => $this->createFormData($request));

        if (!$this->validate($request, $this->getId())) {
            return $this->findView(null, $data);
        }

        $plugin = $this->getPlugin();
        $template = $plugin->get('emailTemplate');
        if (empty($emailTemplate)) {
            $emailTemplate = $this->getId();
        }
        $this->sendNotificationEmail($request->getParameterMap(), $emailTemplate, $plugin->get('adminEmail'));
        $this->messageService->success(_zm("Request submitted!"));

        return $this->findView('success', $data);
    }

    /**
     * Send notification email.
     *
     * @param array data The form data.
     * @param string template The template.
     * @param string email The email address.
     */
    protected function sendNotificationEmail($data, $template, $email) {
        if (empty($email)) {
            $email = ZMSettings::get('storeEmail');
        }

        $message = $this->container->get('messageBuilder')->createMessage($template, true, $request, array('data' => $data, 'id' => $this->getId()));
        $message->setSubject(sprintf(_zm("Form Handler notification: %s"), $this->getId()))->setTo($email)->setFrom(ZMSettings::get('storeEmail'));
        $this->container->get('mailer')->send($message);
    }

    /**
     * Get the plugin.
     *
     * @return ZMPlugin The plugin.
     */
    protected function getPlugin() {
        return $this->container->get('pluginService')->getPluginForId('formHandler');
    }

}
