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
 * Controller for contact us age.
 *
 * @author DerManoMann
 * @package org.zenmagick.rp.uip.controller
 * @version $Id$
 */
class ZMContactUsController extends ZMController {

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
     * Process a HTTP request.
     *
     * <p>Supported request methods are <code>GET</code> and <code>POST</code>.</p>
     *
     * @return ZMView A <code>ZMView</code> instance or <code>null</code>.
     */
    function process() { 
        ZMCrumbtrail::instance()->addCrumb(ZMToolbox::instance()->utils->getTitle(null, false));

        return parent::process();
    }

    /**
     * Process a HTTP GET request.
     * 
     * @return ZMView A <code>ZMView</code> that handles presentation or <code>null</code>
     * if the controller generates the contents itself.
     */
    function processGet() {
        $contactInfo = ZMLoader::make("ContactInfo");
        if (ZMRequest::isRegistered()) {
            $account = ZMRequest::getAccount();
            $contactInfo->setName($account->getFullName());
            $contactInfo->setEmail($account->getEmail());

        }
        $this->exportGlobal("zm_contact", $contactInfo);

        return $this->findView();
    }

    /**
     * Process a HTTP POST request.
     * 
     * @return ZMView A <code>ZMView</code> that handles presentation or <code>null</code>
     * if the controller generates the contents itself.
     */
    function processPost() {
        $contactInfo = ZMLoader::make("ContactInfo");
        $contactInfo->populate();
        // not available in case of success redirect!
        $this->exportGlobal("zm_contact", $contactInfo);

        if (!$this->validate('contact_us')) {
            return $this->findView();
        }

        // send email
        $context = array();
        $context['contactInfo'] = $contactInfo;

        zm_mail(zm_l10n_get("Message from %s", ZMSettings::get('storeName')), 'contact_us', $context, ZMSettings::get('storeEmail'), null, $contactInfo->getEmail(), $contactInfo->getName());

        return $this->findView('success');
    }

}

?>
