<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006 ZenMagick
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
 * @author mano
 * @package net.radebatz.zenmagick.uip.controller
 * @version $Id$
 */
class ZMContactUsController extends ZMController {

    /**
     * Default c'tor.
     */
    function ZMContactUsController() {
        parent::__construct();
    }

    /**
     * Default c'tor.
     */
    function __construct() {
        $this->ZMContactUsController();
    }

    /**
     * Default d'tor.
     */
    function __destruct() {
        parent::__destruct();
    }


    // process a GET request
    function processGet() {
    global $zm_request, $zm_crumbtrail;

        $zm_crumbtrail->addCrumb(zm_title(false));

        $view =& new ZMThemeView("contact_us");
        $this->exportGlobal("zm_contact", $this->create("ContactInfo"));
        if ('success' == $zm_request->getRequestParameter('action')) {
            $view =& new ZMThemeView("contact_us_success");
        }

        return $view;
    }


    // process a POST request
    function processPost() {
    global $zm_request, $zm_crumbtrail;

        $zm_crumbtrail->addCrumb(zm_nice_page_name());
        $contactInfo =& $this->create("ContactInfo");
        $contactInfo->populateFromRequest();
        $this->exportGlobal("zm_contact", $contactInfo);

        return new ZMThemeView('contact_us');
    }

}

?>
