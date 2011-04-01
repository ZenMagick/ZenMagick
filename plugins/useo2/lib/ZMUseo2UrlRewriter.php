<?php
/*
 * ZenMagick - Smart e-commerce
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

use zenmagick\http\request\rewriter\UrlRewriter;

/**
 * USEO2 rewriter.
 *
 * @package org.zenmagick.plugins.useo2
 * @author mano
 */
class ZMUseo2UrlRewriter implements UrlRewriter {

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
        $requestId = $args['requestId'];
        $params = $args['params'];
        $secure = $args['secure'];
        $addSessionId = isset($args['addSessionId']) ? $args['addSessionId'] : true;
        $isStatic = isset($args['isStatic']) ? $args['isStatic'] : false;
        $useContext = isset($args['useContext']) ? $args['useContext'] : true;

        /* QUICK AND DIRTY WAY TO DISABLE REDIRECTS ON PAGES WHEN SEO_URLS_ONLY_IN is enabled IMAGINADW.COM */
        $sefu = explode(",", preg_replace('/ +/', '', SEO_URLS_ONLY_IN));
        if ((SEO_URLS_ONLY_IN != "" && !in_array($requestId, $sefu)) || (null != ZMSettings::get('plugins.useo2.seoEnabled') && !ZMLangUtils::inArray($requestId, ZMSettings::get('plugins.useo2.seoEnabled')))) {
            return null;
        }

        if (!isset($GLOBALS['seo_urls']) || !is_object($GLOBALS['seo_urls'])) {
            $GLOBALS['seo_urls'] = new SEO_URL($_SESSION['languages_id']);
        }

        // no $seo parameter
        return $GLOBALS['seo_urls']->href_link($requestId, $params, $secure ? 'SSL' : 'NONSSL', $addSessionId, $isStatic, $useContext);
    }

}
