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
 * Theme info for default theme.
 *
 * $Id$
 */
class DefaultThemeInfo extends ZMThemeInfo {

    /**
     * Default c'tor.
     */
    function DefaultThemeInfo() {
        parent::__construct();

        $this->setName('Default');
        $this->setVersion('0.7');
        $this->setAuthor('ZenMagick 2006-2007');
        $this->setDescription('ZenMagick default theme; based on andreas08 from http://andreasviklund.com/templates');

        $this->setErrorPage('error');
        $this->setDefaultLayout('default_layout');

        // popups use their own simple page layout
        $this->setLayout('popup_cvv_help', 'popup_layout');
        $this->setLayout('popup_search_help', 'popup_layout');
        $this->setLayout('popup_shipping_estimator', 'popup_layout');

        // set JS event handler
        $this->setPageEventHandler('onload', 'login', "focus('email_address');");
    }

    /**
     * Default c'tor.
     */
    function __construct() {
        $this->DefaultThemeInfo();
    }
    
}

?>
