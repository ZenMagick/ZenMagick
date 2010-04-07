<?php
/*
 * ZenMagick - Another PHP framework.
 * Copyright (C) 2006-2010 zenmagick.org
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
 * Default SEO rewriter.
 *
 * @author DerManoMann
 * @package org.zenmagick.mvc.seo
 * @version $Id$
 */
class ZMDefaultSeoRewriter implements ZMSeoRewriter {
    private $requestIdKey_;
    private $decodeMethod_;
    private $rewriteMethod_;


    /**
     * Create new instance.
     */
    public function __construct() {
        // resolve once only
        $this->requestIdKey_ = ZMSettings::get('zenmagick.mvc.request.idName', ZMRequest::DEFAULT_REQUEST_ID);

        //todo: decide which implementation to use...
        $this->decodeMethod_ = null;
        $this->rewriteMethod_ = 'rewriteDefault';
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        //parent::__destruct();
    }


    /**
     * {@inheritDoc}
     */
    public function decode($request) {
        if (!empty($this->decodeMethod_)) {
            $method = $this->decodeMethod_;
            return $this->$method($request);
        }

        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function rewrite($request, $args) {
        $requestId = $args['requestId'];
        $params = $args['params'];
        $secure = $args['secure'];

        $method = $this->rewriteMethod_;
        return $this->$method($request, $requestId, $params, $secure);
    }

    /**
     * Rewrite default implementation using query parameter.
     *
     * @param ZMRequest request The current request.
     * @param string requestId The request id.
     * @param string params Optional parameter.
     * @param boolean secure Indicate whether to create a secure or non secure URL.
     * @return string The URL.
     */
    protected function rewriteDefault($request, $requestId, $params, $secure) {
        $url = 'index.php?' . $this->requestIdKey_ . '=' . $requestId;
        if (!empty($params)) {
            $url .= '&'.$params;
        }

        if ($secure) {
            $url = $request->absoluteURL($url, false, $secure);
        }

        return $url;
    }

}
