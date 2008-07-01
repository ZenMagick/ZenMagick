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
 * Redirect view.
 *
 * @author mano
 * @package org.zenmagick.rp.uip.views
 * @version $Id$
 */
class ZMRedirectView extends ZMView {
    protected $page_;
    protected $secure_;
    protected $url_;
    protected $parameter_;


    /**
     * Create a new redirect view.
     *
     * @param string page The page (view) name.
     * @param boolean secure Flag whether to redirect using a secure URL or not; default is <code>false</code>.
     */
    function __construct($page, $secure=false) {
        parent::__construct($page);
        $this->page_ = $page;
        $this->secure_ = $secure;
        $this->url_ = null;
        $this->parameter_ = '';
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
     * @return boolean <code>true</code> if the redirect url is not empty.
     */
    public function isValid() {
        return !empty($this->page_);
    }

    /**
     * Generate view response.
     */
    public function generate() { 
        $url = null;
        if (null != $this->url_) {
            $url = $this->url_;
        } else {
            $url = ZMToolbox::instance()->net->url($this->page_, $this->parameter_, $this->secure_, false);
        }

        ZMRequest::redirect($url);
    }

    /**
     * Set additional parameter.
     *
     * @param string parameter Parameter string in URL query format.
     */
    public function setParameter($parameter) {
        $this->parameter_ = $parameter;
    }

    /**
     * Set secure flag.
     *
     * @param boolean secure <code>true</code> to create a secure redirect.
     */
    public function setSecure($secure) {
        $this->secure_ = ZMTools::asBoolean($secure);
    }

    /**
     * Set a url.
     *
     * <p>Setting a url will override the page property. The URL will be used <em>as is</em>.</p>
     *
     * @param string url A full URL.
     */
    public function setUrl($url) {
        $this->url_ = $url;
    }

}

?>
