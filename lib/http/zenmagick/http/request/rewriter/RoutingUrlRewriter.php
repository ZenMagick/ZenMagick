<?php
/*
 * ZenMagick - Another PHP framework.
 * Copyright (C) 2006-2011 zenmagick.org
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
namespace zenmagick\http\request\rewriter;

use zenmagick\base\Runtime;

/**
 * Routing URL rewriter.
 *
 * @author DerManoMann <mano@zenmagick.org>
 * @package zenmagick.http.request.rewriter
 */
class RoutingUrlRewriter implements UrlRewriter {

    /**
     * {@inheritDoc}
     */
    public function decode($request) {
        // try router first
        $routerMatch = $request->getRouterMatch();

        if (null != $routerMatch) {
            $request->setRequestId($routerMatch['_route']);
            $parameterMap = $request->getParameterMap();
            // grab things not set and not prefixed with '_'
            foreach ($routerMatch as $key => $value) {
                if ('_' != $key[0] && !array_key_exists($key, $parameterMap)) {
                    $request->setParameter($key, $value);
                }
            }
            return true;
        }

        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function rewrite($request, $args) {
        if (Runtime::getSettings()->get('zenmagick.http.routing.enabled', false)) {
            $requestId = $args['requestId'];
            $router = $request->getRouter();
            if (null != ($route = $router->getRouteCollection()->get($requestId))) {
                $requirements = array('_scheme' => $args['secure'] ? 'https' : 'http');
                parse_str($args['params'], $parameters);
                // use generator directly to avoid having to customize that as well
                return $router->getGenerator()->generate($requestId, $parameters, false, $requirements);
            }
        }

        return false;
    }

}
