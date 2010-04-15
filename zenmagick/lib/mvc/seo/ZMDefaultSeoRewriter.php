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
    private static $methodList_ = array(
        'default' => array('decode' => null, 'rewrite' => 'rewriteDefault'),
        'path' => array('decode' => 'decodePath', 'rewrite' => 'rewritePath')
    );
    private $methods_;


    /**
     * Create new instance.
     */
    public function __construct() {
        // resolve once only
        $this->requestIdKey_ = ZMSettings::get('zenmagick.mvc.request.idName', ZMRequest::DEFAULT_REQUEST_ID);
        $type = ZMSettings::get('zenmagick.mvc.seo.type', 'default');
        if (!array_key_exists($type, self::$methodList_)) {
            $type = 'default';
        }

        $this->methods_ = self::$methodList_[$type];
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
        if (null != ($method = $this->methods_['decode'])) {
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

        if (null != ($method = $this->methods_['rewrite'])) {
            return $this->$method($request, $requestId, $params, $secure);
        }

        return false;
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

        return $request->absoluteURL($url, false, $secure);
    }

    /**
     * Decode path implementation.
     *
     * @param ZMRequest request The current request.
     * @return boolean <code>true</code> if decoded, <code>false</code> if not.
     */
    protected function decodePath($request) {
        $uri = $request->getUri();
        $context = $request->getContext();
        if (0 === strpos($uri, $context.'index.php/') && false === strpos($uri, '?')) {
            $path = substr($uri, strlen($context.'index.php/'));
            $token = explode('/', $path);
            $tokenCount = count($token);
            if (1 == $tokenCount%2) {
                $request->setRequestId($token[0]);
                for ($ii=1; $ii<$tokenCount; $ii+=2) {
                    $request->setParameter($token[$ii], $token[$ii+1]);
                }
                return true;
            }
        }

        return false;
    }

    /**
     * Rewrite path implementation using something like 'index.php/foo/value-of-foo/bar/value-of-bar'.
     *
     * @param ZMRequest request The current request.
     * @param string requestId The request id.
     * @param string params Optional parameter.
     * @param boolean secure Indicate whether to create a secure or non secure URL.
     * @return string The URL.
     */
    protected function rewritePath($request, $requestId, $params, $secure) {
        $url = 'index.php/' . $requestId;
        parse_str($params, $parr);
        
        foreach ($parr as $key => $value) {
            $url .= '/'.ZMNetUtils::encode($key).'/'.ZMNetUtils::encode($value);
        }

        return $request->absoluteURL($url, false, $secure);
    }

}
