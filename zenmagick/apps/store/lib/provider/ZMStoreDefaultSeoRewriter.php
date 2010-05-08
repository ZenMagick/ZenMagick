<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2010 zenmagick.org
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
 * Default rewriter implementing the original zencart URL scheme.
 *
 * @author DerManoMann
 * @package org.zenmagick.store.provider
 * @version $Id$
 */
class ZMStoreDefaultSeoRewriter implements ZMSeoRewriter {

    /**
     * {@inheritDoc}
     */
    public function decode($request) {
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function rewrite($request, $args) {
        // allow seo here to be able to provide the full set of parameters to SEO plugins
        // this means that in practice this will be the only rewriter called...
        return self::furl($args['requestId'], $args['params'], $args['secure'] ? 'SSL' : 'NONSSL', true, true, false, true, $request);
    }

    /**
     * ZenMagick implementation of zen-cart's zen_href_link function.
     */
    public static function furl($page=null, $params='', $transport='NONSSL', $addSessionId=true, $seo=true, $isStatic=false, $useContext=true, $request=null) {
    //TODO:
    global $session_started, $http_domain, $https_domain;

        if (null == $request) { $request = ZMRequest::instance(); }

        $isAdmin = false;
        if (ZMSettings::get('isAdmin')) {
            // admin links!
            $isAdmin = true;
            //TODO: init!
            if (empty($page)) {
                if (!isset($PHP_SELF)) $PHP_SELF = $_SERVER['PHP_SELF'];
                while (false !== strpos($PHP_SELF, '//')) $PHP_SELF = str_replace('//', '/', $PHP_SELF);
                $page = $PHP_SELF;
            } else {
                $page = DIR_WS_ADMIN . $page;
            }
            $useContext = false;
            $isStatic = true;
        } else if (empty($page)) {
            throw new ZMException('missing page parameter');
        }

        // also do process all rewriters as here we have the full context incl. add. zencart parameters
        // if called directly (as done from the override zen_href_link function...)
        $rewriters = $request->getSeoRewriter();
        if (!$isAdmin && $seo && 0 < count($rewriters)) {
            $rewrittenUrl = null;
            $args = array(
              'requestId' => $page, 
              'params' => $params, 
              'secure' => 'SSL'==$transport, 
              'addSessionId' => $addSessionId, 
              'isStatic' => $isStatic, 
              'useContext' => $useContext
            );
            foreach ($rewriters as $rewriter) {
                if ($rewriter instanceof ZMStoreDefaultSeoRewriter) {
                    // ignore self
                    continue;
                }
                if (null != ($rewrittenUrl = $rewriter->rewrite($request, $args))) {
                    return $rewrittenUrl;
                }
            }
        }

        // default to non ssl
        $server = HTTP_SERVER;
        if ($transport == 'SSL' && ZMSettings::get('isEnableSSL')) {
            $server = HTTPS_SERVER;
        }

        $path = '';
        if ($useContext) {
            $path = HTTPS_SERVER == $server ? DIR_WS_HTTPS_CATALOG : $request->getContext().'/';
        }

        // trim '?' and '&' from params
        while ('?' == ($char = substr($params, 0, 1)) || '&' == $char) $params = substr($params, 1);
        while ('?' == ($char = substr($params, -1)) || '&' == $char) $params = substr($params, 0, -1);

        $query = '?';
        if ($isStatic) {
            $path .= $page;
        } else {
            $path .= 'index.php';
            $query .= ZM_PAGE_KEY . '=' . $page;
        }

        if (!empty($params)) {
            if ( $query !== '?' ) {
                $query .= '&';
            }
            $query .= strtr(trim($params), array('"' => '&quot;'));
        }

        // trim trailing '?' and '&' from path
        while ('?' == ($char = substr($path, -1)) || '&' == $char) $path = substr($path, 0, -1);

        // Add the session ID when moving from different HTTP and HTTPS servers, or when SID is defined
        $sid = null;
        //TODO:$session = $request->getSession();
        if ($addSessionId && ($session_started/* || $session->isStarted()*/) && !ZMSettings::get('isForceCookieUse')) {
            if (defined('SID') && !ZMLangUtils::isEmpty(SID)) {
                // defined, so use it
                $sid = SID;
            } elseif (($transport == 'NONSSL' && HTTPS_SERVER == $server) || ($transport == 'SSL' && HTTP_SERVER == $server)) {
                // switch from http to https or vice versa
                if ($http_domain != $https_domain) {
                    $sid = zen_session_name() . '=' . zen_session_id();
                }
            }
        }

        if (null !== $sid) {
            $query .= '&' . strtr(trim($sid), array('"' => '&quot;'));
        }

        while (false !== strpos($path, '//')) $path = str_replace('//', '/', $path);
        $query = (1 < strlen($query)) ? $query : '';

        return $request->getToolbox()->net->encode($server.$path.$query);
    }

}
