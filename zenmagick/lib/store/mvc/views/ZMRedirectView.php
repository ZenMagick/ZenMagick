<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2009 ZenMagick
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
 * @author DerManoMann
 * @package org.zenmagick.store.mvc.views
 * @version $Id: ZMRedirectView.php 2308 2009-06-24 11:03:11Z dermanomann $
 */
class ZMRedirectView extends ZMView {
    protected $secure_;
    protected $url_;
    protected $parameter_;
    protected $status_;


    /**
     * Create a new redirect view.
     *
     * @param string view The view name.
     * @param boolean secure Flag whether to redirect using a secure URL or not; default is <code>false</code>.
     * @deprecated: contructor arguments
     */
    function __construct($view=null, $secure=false) {
        parent::__construct($view);
        $this->secure_ = $secure;
        $this->url_ = null;
        $this->parameter_ = '';
        $this->status_ = 302;
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
        $view = $this->view_;
        return !empty($view);
    }

    /**
     * Generate view response.
     *
     * @param ZMRequest request The current request.
     */
    public function generate($request) { 
        $url = null;
        if (null != $this->url_) {
            $url = $this->url_;
        } else {
            $url = ZMToolbox::instance()->net->url($this->view_, $this->parameter_, $this->secure_, false);
        }

        ZMRequest::instance()->redirect($url, $this->status_);
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
        $this->secure_ = ZMLangUtils::asBoolean($secure);
    }

    /**
     * Set a url.
     *
     * <p>Setting a url will override the view. The URL will be used <em>as is</em>.</p>
     *
     * @param string url A full URL.
     */
    public function setUrl($url) {
        $this->url_ = $url;
    }

    /**
     * Set alternative status code.
     *
     * <p>Allows to set an alternative 3xx status code for the redirect.</p>
     *
     * @param int status HTTP status code.
     */
    public function setStatus($status) {
        $this->status_ = (int)$status;
    }
}

?>
