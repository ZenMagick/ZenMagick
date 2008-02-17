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
 * Popup view.
 *
 * @author mano
 * @package org.zenmagick.rp.uip.views
 * @version $Id$
 */
class ZMPopupView extends ZMPageView {

    /**
     * Create new popup view.
     *
     * @param string page The page (view) name.
     */
    function ZMPopupView($page) {
        parent::__construct($page);
    }

    /**
     * Create new popup view.
     *
     * @param string page The page (view) name.
     */
    function __construct($page) {
        $this->ZMPopupView($page);
    }

    /**
     * Default d'tor.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Returns the full view filename to be includes by a template.
     *
     * @return string The full view filename.
     */
    function getViewFilename() {
        return $this->_getViewFilename('popup');
    }

    /**
     * Check if this view is valid.
     *
     * @return boolean <code>true</code> if the view is valid, <code>false</code> if not.
     */
    function isValid() {
        return file_exists($this->_getViewFilename('popup'));
    }

}

?>
