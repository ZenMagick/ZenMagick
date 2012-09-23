<?php
/*
 * ZenMagick - Smart e-commerce
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
namespace ZenMagick\apps\storefront\Http\Request;

use ZenMagick\Base\Runtime;
use ZenMagick\Base\Toolbox;
use ZenMagick\Base\ZMObject;
use ZenMagick\Base\ZMException;
use ZenMagick\Http\Request\UrlRewriter;

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
     *
     * @todo improve this entirely!
     */
    public static function furl($page, $params='', $transport='NONSSL', $addSessionId=true, $seo=true, $isStatic=false, $useContext=true) {

        $container = Runtime::getContainer();
        $request = $container->get('request');

        $page = trim($page, '&?');
        $page = str_replace('&amp;', '&', $page);

        $params = trim(trim($params), '&?');
        $params = str_replace('&amp;', '&', $params);

        if ('index.php' == $page) $page = 'index';
        if ('ipn_main_handler.php' == $page) $page = 'ipn';

        $requestId = $page;
        parse_str($params, $parameters);
        if (0 === strpos($page, 'index.php?')) { // EZPage altUrl
            $page = str_replace('index.php?', '', $page);
            parse_str($page, $extra);
            if (array_key_exists('main_page', $extra)) {
                $requestId = $extra['main_page'];
                unset($extra['main_page']);
            }
            $parameters = array_merge($extra, $parameters);
        }
        // @todo if we still keep someting like this.. wrong place!
        if (array_key_exists('products_id', $parameters)) {
            $parameters['productId'] = $parameters['products_id'];
            unset ($parameters['products_id']);
        }

        return $container->get('router')->generate($requestId, $parameters);
    }

}
