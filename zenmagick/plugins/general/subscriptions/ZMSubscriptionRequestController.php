<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2010 zenmagick.org
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
 * @package org.zenmagick.plugins.subscriptions
 * @version $Id: ZMSubscriptionRequestController.php 2696 2009-12-04 00:06:09Z dermanomann $
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
     * @return ZMObject The model.
     */
    protected function createModel() {
        $request = new ZMObject();
        $request->set('type', ZMRequest::instance()->getParameter('type'));
        $request->set('orderId', ZMRequest::instance()->getParameter('orderId'));
        $request->set('message', ZMRequest::instance()->getParameter('message'));
        $request->set('types', $this->getPlugin()->getRequestTypes());

        return $request;
    }

    /**
     * {@inheritDoc}
     */
    public function processGet($request) {
        return $this->findView(null, array('subscriptionRequest' => $this->createModel()));
    }

    /**
     * {@inheritDoc}
     */
    public function processPost($request) {
        $data = array('subscriptionRequest' => $this->createModel());
        if (!$this->validate($request, 'subscription_request')) {
            return $this->findView(null, $data);
        }

        $plugin = $this->getPlugin();
        $emailTemplate = ZMSettings::get('plugins.subscriptions.email.templates.request', 'subscription_request');
        $this->sendNotificationEmail($request->getParameterMap(), $emailTemplate, $plugin->get('adminEmail'));

        ZMMessages::instance()->success(_zm("Request submitted!"));

        return $this->findView('success', $data);
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
        zm_mail(sprintf(_zm("Subscription request notification"), ZMSettings::get('storeName')), $template, $context, 
            $email, ZMSettings::get('storeEmail'), null);
    }

    /**
     * Get the plugin.
     *
     * @return ZMPlugin The plugin.
     */
    protected function getPlugin() {
        return ZMPlugins::instance()->getPluginForId('subscriptions');
    }

}
