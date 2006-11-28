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
 * @package net.radebatz.zenmagick.controller
 * @version $Id$
 */
class ZMContactUsController extends ZMController {

    // create new instance
    function ZMContactUsController() {
        parent::__construct();
    }

    // create new instance
    function __construct() {
        $this->ZMContactUsController();
    }

    function __destruct() {
    }


    /** API implementation */

    // process a GET request
    function processGet() {
    global $zm_request, $zm_crumbtrail;

        $zm_crumbtrail->addCrumb(zm_title(false));

        $this->exportGlobal("zm_contact", new ZMContactInfo());
        if ('success' == $zm_request->getRequestParameter('action')) {
            $this->setResponseView(new ZMView("contact_us_success", "contact_us_success"));
        }

        return true;
    }

    // process a POST request
    function processPost() {
    global $zm_request, $zm_crumbtrail;

        $zm_crumbtrail->addCrumb(zm_nice_page_name());
        $contactInfo = new ZMContactInfo();
        $contactInfo->populateFromRequest();
        $this->exportGlobal("zm_contact", $contactInfo);

        return true;
    }

}

?>
