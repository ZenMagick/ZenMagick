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
 *
 * $Id$
 */
?>
<?php

/**
 * Theme info for default theme.
 */
class DefaultThemeInfo extends ZMThemeInfo {

    // create new instance
    function DefaultThemeInfo() {
        parent::__construct();
        $this->setName('Default');
        $this->setVersion('0.4');
        $this->setAuthor('ZenMagick 2006');
        $this->setDescription('ZenMagick default theme; based on andreas08 from http://andreasviklund.com/templates');

        $this->setErrorPage('error');
        $this->setDefaultLayout('default_layout');

        // configure individual layout templates
        //$this->setLayout('static', 'special_layout');

        // keep error page simple
        $this->setLayout('error', null);

        // set default JS event handler; i.e. for ALL pages
        //$this->setDefaultPageEventHandler('onload', "inject_category_code();");

        // set JS event handler
        $this->setPageEventHandler('onload', 'login', "focus('email_address');");
    }

    // create new instance
    function __construct() {
        $this->DefaultThemeInfo();
    }
    
}

?>
