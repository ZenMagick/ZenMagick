<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006,2007 ZenMagick
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
 * Redirect view.
 *
 * @author mano
 * @package net.radebatz.zenmagick.rp.uip.views
 * @version $Id$
 */
class ZMRedirectView extends ZMView {
    var $page_;
    var $secure_;
    var $parameter_;


    /**
     * Create a new redirect view.
     *
     * @param string page The page (view) name.
     * @param boolean secure Flag whether to redirect using a secure URL or not; default is <code>false</code>.
     */
    function ZMRedirectView($page, $secure=false) {
        parent::__construct($page);

        $this->page_ = $page;
        $this->secure_ = $secure;
        $this->parameter_ = '';
    }

    /**
     * Create a new redirect view.
     *
     * @param string page The page (view) name.
     * @param boolean secure Flag whether to redirect using a secure URL or not; default is <code>false</code>.
     */
    function __construct($page, $secure=false) {
        $this->ZMRedirectView($page, $secure);
    }

    /**
     * Default d'tor.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Check if this view is valid.
     *
     * @return boolean <code>true</code> if the redirect url is not empty.
     */
    function isValid() {
        return !zm_is_empty($this->page_);
    }

    /**
     * Generate view response.
     */
    function generate() { 
        $url = null;
        if ($this->secure_) {
            $url = zm_secure_href($this->page_, $this->parameter_, false);
        } else {
            $url = zm_href($this->page_, $this->parameter_, false);
        }

        zm_redirect($url);
    }

    /**
     * Set additional parameter.
     *
     * @param string parameter Parameter string in URL query format.
     */
    function setParameter($parameter) {
        $this->parameter_ = $parameter;
    }

    /**
     * Set secure flag.
     *
     * @param boolean secure <code>true</code> to create a secure redirect.
     */
    function setSecure($secure) {
        $this->secure_ = $secure;
    }

}

?>
