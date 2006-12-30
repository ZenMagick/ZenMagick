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
 * Theme info for demo theme.
 *
 * $Id$
 */
class DemoThemeInfo extends ZMThemeInfo {

    // create new instance
    function DemoThemeInfo() {
        parent::__construct();
        $this->setName('ProductDemo');
        $this->setVersion('0.1');
        $this->setAuthor('ZenMagick 2006');
        $this->setDescription('ZenMagick demo theme including additional product pages and extra code.');

        $this->setLayout('popup_cvv_help', 'popup_layout');
        $this->setLayout('popup_search_help', 'popup_layout');
        $this->setLayout('popup_shipping_estimator', 'popup_layout');
    }

    // create new instance
    function __construct() {
        $this->DemoThemeInfo();
    }
    
}

?>
