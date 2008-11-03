<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2008 ZenMagick
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
 * Generic form handler controller.
 *
 * @author DerManoMann
 * @package org.zenmagick.plugins.zm_form_handler
 * @version $Id$
 */
class ZMFormHandlerController extends ZMController {

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
     * Create the model from the current request.
     *
     * @return ZMModel The model.
     */
    protected function createModel() {
        $request = ZMLoader::make('Model');
        foreach (ZMRequest::getParameterMap() as $name => $value) {
            $request->set($name, $value);
        }

        return $request;
    }

    /**
     * Process a HTTP GET request.
     * 
     * @return ZMView A <code>ZMView</code> that handles presentation or <code>null</code>
     * if the controller generates the contents itself.
     */
    public function processGet() {
        // create model
        $this->exportGlobal('formData', $this->createModel());
        return $this->findView();
    }

    /**
     * Process a HTTP POST request.
     * 
     * @return ZMView A <code>ZMView</code> that handles presentation or <code>null</code>
     * if the controller generates the contents itself.
     */
    public function processPost() {
        // create model
        $this->exportGlobal('formData', $this->createModel());

        if (!$this->validate($this->getId())) {
            return $this->findView();
        }

        $plugin = $this->getPlugin();
        $template = $plugin->get('emailTemplate');
        if (empty($emailTemplate)) {
            $emailTemplate = $this->getId();
        }
        $this->sendNotificationEmail(ZMRequest::getParameterMap(), $emailTemplate, $plugin->get('adminEmail'));
        ZMMessages::instance()->success(zm_l10n_get("Request submitted!"));

        return $this->findView('success');
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
        zm_mail(zm_l10n_get("Form Handler notification: %s", $this->getId()), $template, array('data' => $data, 'id' => $this->getId()), 
            $email, ZMSettings::get('storeEmail'), null);
    }

    /**
     * Get the plugin.
     *
     * @return ZMPlugin The plugin.
     */
    protected function getPlugin() {
        return ZMPlugins::instance()->getPluginForId('zm_form_handler');
    }

}

?>
