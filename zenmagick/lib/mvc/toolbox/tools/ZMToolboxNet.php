<?php
/*
 * ZenMagick - Another PHP framework.
 * Copyright (C) 2006,2009 ZenMagick
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
 * Networking/URL related functions.
 *
 * @author DerManoMann
 * @package org.zenmagick.mvc.utils
 * @version $Id$
 */
class ZMToolboxNet extends ZMToolboxTool {
    private $seoRewriter_ = null;


    /**
     * Get a list of <code>ZMSeoRewriter</code> instances for SEO urls.
     *
     * <p>The list is build based on the classes registered via the setting
     * 'zenmagick.mvc.request.seoRewriter'.</p>
     *
     * @return array List of <code>ZMSeoRewriter</code> instances.
     */
    protected function getSeoRewriter() {
        if (null === $this->seoRewriter_) {
            foreach (explode(',', ZMSettings::get('zenmagick.mvc.request.seoRewriter')) as $rewriter) {
                if (null != ($obj = ZMBeanUtils::getBean($rewriter))) {
                    $this->seoRewriter_[] = $obj;
                }
            }
        }

        return $this->seoRewriter_;
    }

    /**
     * Create a URL.
     *
     * <p>Mother of all URL related methods.</p>
     *
     * <p>If the <code>requestId</code> parameter is <code>null</code>, the current requestId will be
     * used. The provided parameter will be merged into the current query string.</p>
     *
     * <p>If the <code>params</code> parameter is <code>null</code>, all parameters of the
     * current request will be added.</p>
     *
     * <p>This default implementation relies on at least a single (default) SEO rewriter being configured.</p>
     *
     * @param string requestId The request id.
     * @param string params Query string style parameter; if <code>null</code> add all current parameter
     * @param boolean secure Flag indicating whether to create a secure or non secure URL; default is <code>false</code>.
     * @return string A full URL.
     */
    public function url($requestId=null, $params='', $secure=false) {
        // custom requestId and params handling
        if (null === $requestId || null === $params) {
            $query = $this->getRequest()->getParameterMap();
            unset($query[ZMRequest::REQUEST_ID]);
            unset($query[$request->getSession()->getName()]);
            if (null != $params) {
                parse_str($params, $arr);
                $query = array_merge($query, $arr);
            }
            $params = array();
            foreach ($query as $name => $value) {
                if (is_array($value)) {
                    foreach ($value as $subValue) {
                        $params[] = $name.'[]='.$subValue;
                    }
                } else {
                    $params[] = $name.'='.$value;
                }
            }
        }

        // default to current requestId
        $requestId = $requestId === null ? $this->getRequest()->getRequestId() : $requestId;

        // handle SEO
        $rewriters = $this->getSeoRewriter();
        if (!$isAdmin && $seo && 0 < count($rewriters)) {
            $rewrittenUrl = null;
            $args = array(
              'requestId' => $page,
              'params' => implode('&', $params),
              'secure' => 'SSL'==$transport,
              'addSessionId' => $addSessionId,
              'isStatic' => $isStatic,
              'useContext' => $useContext
            );
            foreach ($rewriters as $rewriter) {
                if (null != ($rewrittenUrl = $rewriter->rewrite($this->getRequest(), $args))) {
                    return $rewrittenUrl;
                 }
            }
        }

        ZMLogging::instance()->trace('unresolved URL: '.$requestId);
        return null;
    }

    /**
     * Convert a given relative URL into an absolute one.
     *
     * @param string url The (relative) URL to convert.
     * @param boolean full Set to true to create a full URL incl. the protocol, hostname, port, etc.; default is <code>false</code>.
     * @return string The absolute URL.
     */
    public function absoluteURL($url, $full=false) {
        $request = $this->getRequest();
        $url = ('/' == $url[0] || false !== strpos($url, '://')) ? $url : $request->getContext().$url;

        if ($full) {
            // todo: move somewhere reusable
            $scheme = $request->isSecure() ? 'https://' : 'http://';
            $host = $request->getHostname();
            $port = $request->getPort();
            if ('80' == $port && !$request->isSecure() || '443' == $port && $request->isSecure()) {
                $port = '';
            } else {
                $port = ':'.$port;
            }

            $url = $scheme.$host.$port.$url;
        }

        return $url;
    }

    /**
     * Encode a given URL to valid HTML.
     *
     * @param string url The url to encode.
     * @return string The URL encoded in valid HTM.
     */
    public function encode($url) {
      //todo: zm_i18n...
        $url = htmlentities($url, ENT_QUOTES, zm_i18n('HTML_CHARSET'));
        $url = str_replace(' ', '%20', $url);
        return $url;
    }

    /**
     * Decode a HTML encoded URL.
     *
     * @param string url The url to decode.
     * @return string The decoded URL.
     */
    public function decode($url) {
      //todo: zm_i18n...
        $s = html_entity_decode($url, ENT_QUOTES, zm_i18n('HTML_CHARSET'));
        $s = str_replace('%20', ' ', $s);
        return $s;
    }

}

?>
