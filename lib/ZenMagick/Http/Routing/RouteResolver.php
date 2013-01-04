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
namespace ZenMagick\Http\Routing;

use ZenMagick\Base\Beans;
use ZenMagick\Base\ZMObject;
use ZenMagick\Http\View\TemplateView;

/**
 * ZenMagick routing API.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class RouteResolver extends ZMObject
{
    const GLOBAL_ROUTING_KEY = 'zenmagick_global_routing';

    /**
     * Get a route for the given uri.
     *
     * @param string uri The uri.
     * @return mixed The route or <code>null</code>.
     */
    public function getRouteForUri($uri)
    {
        if (null != ($routerMatch = $this->getRouter()->match($uri))) {
            return $this->container->get('router')->getRouteCollection()->get($routerMatch['_route']);
        }

        return null;
    }

    /**
     * Get a view for the given request and view id.
     *
     * @param string viewId The view id.
     * @param ZenMagick\Http\Request request The current request.
     * @param array Optional view data; default is an empty array.
     * @return View A view.
     * @todo: move into dispatcher and fix controller to return just string/string/data from process
     */
    public function getViewForId($viewId, $request, array $data=array())
    {
        $view = null;
        // build list of routes to look at
        $routeIds = array();
        $routeIds[] = $viewId;
        $routeIds[] = $request->attributes->get('_route');
        $routeIds[] = self::GLOBAL_ROUTING_KEY;

        // check until match or we run out of routeIds
        $settingsService = $this->container->get('settingsService');
        $layoutName = $settingsService->get('zenmagick.http.view.defaultLayout', null);
        foreach ($routeIds as $routeId) {
            if (null != ($route = $this->container->get('router')->getRouteCollection()->get($routeId))) {
                $viewKey = null == $viewId ? 'view' : sprintf('view:%s', $viewId);
                $options = $route->getOptions();
                if (array_key_exists($viewKey, $options)) {
                    $viewDefinition = null;
                    $token = parse_url(str_replace('%routeId%', $request->getRequestId(), $options[$viewKey]));
                    if (!array_key_exists('query', $token)) {
                        $token['query'] = '';
                    }
                    // merge in layout if set
                    if (null != $layoutName) {
                        parse_str($token['query'], $query);
                        if (!array_key_exists('layout', $query)) {
                            $query['layout'] = $layoutName;
                        }
                        // also allow layout as option
                        if (array_key_exists('layout', $options)) {
                            $query['layout'] = $options['layout'];
                        }
                        $token['query'] = http_build_query($query);
                    }
                    if (array_key_exists('scheme', $token)) {
                        // default to same page if nothing set
                        if (!array_key_exists('host', $token)) {
                            $token['host'] = $request->getRequestId();
                        }
                        $viewDefinition = sprintf('%s#requestId=%s&%s', $token['scheme'], $token['host'], $token['query']);
                    } else {
                        $viewDefinition = sprintf('%s#template=%s&%s', 'defaultView', $token['path'], $token['query']);
                    }
                    $view = Beans::getBean($viewDefinition);
                    break;
                }
            }
        }

        if (!$view) { // use conventions and defaults
            $templateName = sprintf('%s%s', $request->getRequestId(), '.html.php');
            $view = Beans::getBean('defaultView');
            $view->setTemplate($templateName);
            $view->setLayout($layoutName);
        }

         if ($view instanceof TemplateView && $data) {
            $view->setVariables($data);
        }

        return $view;
    }

}
