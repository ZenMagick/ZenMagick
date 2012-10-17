<?php
/*
 * ZenMagick - Another PHP framework.
 * Copyright (C) 2006-2012 zenmagick.org
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
namespace ZenMagick\Http\View;

use ZenMagick\Base\Toolbox;
use ZenMagick\Base\ZMObject;

/**
 * Redirect view.
 *
 * <p>The redirect URL may be set by explicitely setting a url or a request Id.
 * If a request Id is set, a full URL will be created.</p>
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class RedirectView extends ZMObject implements View {
    protected $secure;
    protected $url_;
    protected $parameter_;
    protected $status_;
    protected $requestId_;


    /**
     * Create a new redirect view.
     */
    public function __construct() {
        parent::__construct();
        $this->secure = false;
        $this->url_ = null;
        $this->parameter_ = '';
        $this->status_ = 302;
        $this->requestId_ = null;
    }


    /**
     * {@inheritDoc}
     */
    public function isValid() {
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function generate($request, $template=null, $variables=array()) {
        $request->redirect($this->getRedirectUrl($request), $this->status_);
        return null;
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
        $this->secure = Toolbox::asBoolean($secure);
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

    /**
     * Set the request id.
     *
     * @param string requestId Request id of the redirect URL.
     */
    public function setRequestId($requestId) {
        $this->requestId_ = $requestId;
    }

    /**
     * Get the request id of the redirect.
     *
     * <p>If not set, this will default to the template name (compatibility mode).</p>
     *
     * @return string The request id.
     */
    public function getRequestId() {
        return $this->requestId_;
    }

    /**
     * Get the evaluated redirect url.
     *
     * @param ZenMagick\Http\Request request The current request.
     * @return string The redirect url.
     */
    public function getRedirectUrl($request) {
        $secure = $this->secure || $this->container->get('sacsManager')->requiresSecurity($this->getRequestId());
        return ((null != $this->url_) ? $this->url_ : $this->container->get('netTool')->url($this->getRequestId(), $this->parameter_, $secure));
    }

}
