<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2012 zenmagick.org
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
namespace zenmagick\apps\store\storefront\http\request;

use zenmagick\base\Runtime;
use zenmagick\base\Toolbox;
use zenmagick\base\ZMObject;
use zenmagick\base\ZMException;
use zenmagick\http\request\UrlRewriter;

/**
 * Default rewriter implementing the original zencart URL scheme.
 *
 * @author DerManoMann
 */
class StoreDefaultUrlRewriter extends ZMObject implements UrlRewriter {

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
        $secure = $args['secure'];
        // provide the full set of parameters to SEO plugins
        // this means that in practice this will be the only rewriter called...
        return self::furl($args['requestId'], $args['params'], $secure ? 'SSL' : 'NONSSL', true, true, false, true, $request);
    }

    /**
     * ZenMagick implementation of zen-cart's zen_href_link function.
     */
    public static function furl($page=null, $params='', $transport='NONSSL', $addSessionId=true, $seo=true, $isStatic=false, $useContext=true, $request=null) {
        $container = Runtime::getContainer();
        if (null == $request) { $request = $container->get('request'); }

        if (empty($page)) {
            throw new ZMException('missing page parameter');
        }

        // also do process all rewriters as here we have the full context incl. add. zencart parameters
        // if called directly (as done from the override zen_href_link function...)
        $rewriters = $request->getUrlRewriter();
        if ($seo && 0 < count($rewriters)) {
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
                if ($rewriter instanceof StoreDefaultUrlRewriter) {
                    // ignore self
                    continue;
                }
                if (null != ($rewrittenUrl = $rewriter->rewrite($request, $args))) {
                    return $rewrittenUrl;
                }
            }
        }

        // default to non ssl
        $hostname = $request->getHostname();
        $httpServer = 'http://'.$hostname;
        $httpsServer = 'https://'.$hostname;

        $settingsService = $container->get('settingsService');
        $server = $httpServer;
        if ($transport == 'SSL' && $settingsService->get('zenmagick.http.request.secure', true)) {
            $server = $httpsServer;
        }

        $path = '';
        if ($useContext) {
            $path = $request->getContext().'/';
        }

        // trim '?' and '&' from params
        while ('?' == ($char = substr($params, 0, 1)) || '&' == $char) $params = substr($params, 1);
        while ('?' == ($char = substr($params, -1)) || '&' == $char) $params = substr($params, 0, -1);

        $query = '?';
        if ($isStatic) {
            $path .= $page;
        } else {
            $path .= 'index.php';
            $query .= $settingsService->get('zenmagick.http.request.idName') . '=' . $page;
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
        $session = $request->getSession();
        if ($addSessionId && ($session->isStarted()) && !$settingsService->get('isForceCookieUse')) {
            if (defined('SID') && !Toolbox::isEmpty(SID)) {
                // defined, so use it
                $sid = SID;
            } elseif (($transport == 'NONSSL' && $httpsServer == $server) || ($transport == 'SSL' && $httpServer == $server)) {
                // switch from http to https or vice versa
                // @todo revisit this if we really want to support shared certificates
                $http_domain = isset($GLOBALS['http_domain']) ? $GLOBALS['http_domain'] : $hostname;
                $https_domain = isset($GLOBALS['https_domain']) ? $GLOBALS['https_domain'] : $hostname;
                if ($http_domain != $https_domain) {
                    $sid = $session->getName() . '=' . $session->getId();
                }
            }
        }

        if (null !== $sid) {
            $query .= '&' . strtr(trim($sid), array('"' => '&quot;'));
        }

        while (false !== strpos($path, '//')) $path = str_replace('//', '/', $path);
        $query = (1 < strlen($query)) ? $query : '';

        return \ZMNetUtils::encode($server.$path.$query);
    }

}
