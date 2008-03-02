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
 * Forward view.
 *
 * <p>This will forward the request to the given controller without a redirect.</p>
 *
 * @author mano
 * @package org.zenmagick.rp.uip.views
 * @version $Id$
 */
class ZMForwardView extends ZMView {

    /**
     * Create a new forward view.
     *
     * @param string page The page (view) name.
     */
    function ZMForwardView($page) {
        parent::__construct($page);
    }

    /**
     * Create a new forward view.
     *
     * @param string page The page (view) name.
     */
    function __construct($page) {
        $this->ZMForwardView($page);
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Check if this view is valid.
     *
     * @return boolean <code>true</code> if the forward url is not empty.
     */
    function isValid() {
        return !zm_is_empty($this->page_);
    }

    /**
     * Generate view response.
     *
     * <p>Will do the following:</p>
     * <ul>
     *  <li>Reset the crumbtrail</li>
     *  <li>add the forward page as <em>main_page</em> to the request</li>
     *  <li>call <code>zm_dispatch()</code></li>
     * </ul>
     */
    function generate() { 
    global $zm_request;

        ZMCrumbtrail::instance()->reset();
        // TODO: do not use name directly!
        $zm_request->setParameter('main_page', $this->page_);
        zm_dispatch();
        return null;
    }

}

?>
