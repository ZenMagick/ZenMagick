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
 * @todo create store agnostic default implementations
 */
class ZMToolboxNet extends ZMToolboxTool {

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
     * @param string requestId The request id.
     * @param string params Query string style parameter; if <code>null</code> add all current parameter
     * @param boolean secure Flag indicating whether to create a secure or non secure URL; default is <code>false</code>.
     * @param boolean echo If <code>true</code>, the URL will be echo'ed as well as returned.
     * @return string A full URL.
     */
    public function url($requestId=null, $params='', $secure=false, $echo=ZM_ECHO_DEFAULT) {
        // custom view and params handling
        if (null === $requestId || null === $params) {
            $query = $this->getRequest()->getParameterMap();
            unset($query[ZM_PAGE_KEY]);
            //XXX:??
            unset($query[zen_session_name()]);
            if (null != $params) {
                parse_str($params, $arr);
                $query = array_merge($query, $arr);
            }
            $params = '';
            foreach ($query as $name => $value) {
                if (is_array($value)) {
                    foreach ($value as $subValue) {
                        $params .= $name.'[]='.$subValue.'&';
                    }
                } else {
                    $params .= $name.'='.$value.'&';
                }
            }
        }

        // default to current view
        $requestId = $requestId === null ? $this->getRequest()->getRequestId() : $requestId;
        $href = null;
        // no SEO in admin
        // XXX: have separate setting to disable rather than admin (might have to fake that to force regular URLS
        if (function_exists('zm_build_seo_href') && !ZMSettings::get('isAdmin')) {
            // use custom SEO builder function - three args only
            $href = zm_build_seo_href($requestId, $params, $secure);
        } else {
            // use default implementation - three args only
            $href = $this->furl($requestId, $params, $secure ? 'SSL' : 'NONSSL');
        }

        if ($echo) echo $href;
        return $href;
    }

    /**
     * Convert a given relative url into an absolute one.
     *
     * @param string url The (relative) URL to convert.
     * @return string The absolute url.
     */
    public function absolute($url) {
        return ('/' == $url[0] || false !== strpos($url, '://')) ? $url : $this->getRequest()->getContext().$url;
    }

    /**
     * Encode a given URL to valid HTML.
     *
     * @param string url The url to encode.
     * @return string The URL encoded in valid HTM.
     */
    public function encode($url) {
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
        $s = html_entity_decode($url, ENT_QUOTES, zm_i18n('HTML_CHARSET'));
        $s = str_replace('%20', ' ', $s);
        return $s;
    }

}

?>
