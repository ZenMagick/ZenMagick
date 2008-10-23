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
 * Request controller for customer requests.
 *
 * @author DerManoMann
 * @package org.zenmagick.plugins.zm_subscriptions
 * @version $Id$
 */
class ZMSubscriptionRequestController extends ZMController {

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
        $request->set('type', ZMRequest::getParameter('type'));
        $request->set('orderId', ZMRequest::getParameter('orderId'));
        $request->set('message', ZMRequest::getParameter('message'));
        $request->set('types', $this->getPlugin()->getRequestTypes());

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
        $this->exportGlobal('zm_subscriptionRequest', $this->createModel());
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
        $this->exportGlobal('zm_subscriptionRequest', $this->createModel());

        if (!$this->validate('subscription_request')) {
            return $this->findView();
        }

        $plugin = $this->getPlugin();
        $emailTemplate = ZMSettings::get('plugins.zm_subscriptions.email.templates.request', ZM_TEMPLATE_SUBSCRIPTION_REQUEST_NOTIFICATION);
        $this->sendNotificationEmail(ZMRequest::getParameterMap(), $emailTemplate, $plugin->get('adminEmail'));

        ZMMessages::instance()->success(zm_l10n_get("Request submitted!"));

        return $this->findView('success');
    }

    /**
     * Send notification email.
     *
     * @param string template The template.
     * @param string email The email address.
     */
    protected function sendNotificationEmail($context, $template, $email) {
        if (empty($email)) {
            $email = ZMSettings::get('storeEmail');
        }
        zm_mail(zm_l10n_get("Subscription request notification", ZMSettings::get('storeName')), $template, $context, 
            $email, ZMSettings::get('storeEmail'), null);
    }

    /**
     * Get the plugin.
     *
     * @return ZMPlugin The plugin.
     */
    protected function getPlugin() {
        return ZMPlugins::instance()->getPluginForId('zm_subscriptions');
    }

}

?>
