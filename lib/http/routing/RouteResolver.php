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
namespace zenmagick\http\routing;

use Exception;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Router;
use Symfony\Component\Routing\RequestContext as SymfonyRequestContext;

use zenmagick\base\Beans;
use zenmagick\base\Runtime;
use zenmagick\base\ZMObject;
use zenmagick\base\logging\Logging;
use zenmagick\http\routing\loader\YamlLoader;
use zenmagick\http\view\TemplateView;

/**
 * ZenMagick routing API.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class RouteResolver extends ZMObject {
    const GLOBAL_ROUTING_KEY = 'zenmagick_global_routing';
    private $requestContext;
    private $options;
    private $router;
    private $routes;

    /**
     * Create new instance.
     */
    public function __construct(SymfonyRequestContext $requestContext) {
        parent::__construct();
        $this->requestContext = $requestContext;
        $this->options = array(
            'generator_class' => 'zenmagick\http\routing\generator\UrlGenerator',
            //'matcher_class' => 'Symfony\\Component\\Routing\\Matcher\\RedirectableUrlMatcher'
        );
        $this->router = null;
        $this->routes = array();
    }

    /**
     * Get the router.
     *
     * @return Router The router.
     */
    public function getRouter() {
        if (null == $this->router) {
            $this->router = new Router(new YamlLoader(), '', $this->options, $this->requestContext);
        }
        return $this->router;
    }

    /**
     * Get match for the fiven uri.
     *
     * @param string uri The uri to match.
     * @return array The match or <code>null</code>.
     */
    public function getRouterMatch($uri) {
        // check for cache hit
        if (array_key_exists($uri, $this->routes)) {
            return $this->routes[$uri];
        }

        // try router first
        $routerMatch = null;
        try {
            // this doesn't feel right
            $nuri = preg_replace('#^'.$this->requestContext->getBaseUrl().'#', '', $uri);
            $routerMatch = $this->getRouter()->match($nuri);
        } catch (Exception $e) {
            Runtime::getLogging()->dump($e, 'no route found', Logging::TRACE);
        }
        $this->routes[$uri] = $routerMatch;

        return $this->routes[$uri];
    }

    /**
     * Get a route for the given route/request id.
     *
     * @param string routeId The id.
     * @return mixed The route or <code>null</code>.
     */
    public function getRouteForId($routeId) {
        return $this->getRouter()->getRouteCollection()->get($routeId);
    }

    /**
     * Get a route for the given uri.
     *
     * @param string uri The uri.
     * @return mixed The route or <code>null</code>.
     */
    public function getRouteForUri($uri) {
        if (null != ($routerMatch = $this->getRouterMatch($uri))) {
            return $this->getRouteForId($routerMatch['_route']);
        }

        return null;
    }

    /**
     * Get a view for the given request and view id.
     *
     * @param string viewId The view id.
     * @param ZMRequest request The current request.
     * @param array Optional view data; default is an empty array.
     * @return View A view.
     * @todo: move into dispatcher and fix controller to return just string/string/data from process
     */
    public function getViewForId($viewId, $request, array $data=array()) {
        $view = null;
        // build list of routes to look at
        $routeIds = array();
        if (null != ($routerMatch = $this->getRouterMatch($request->getUri()))) {
            $routeIds[] = $routerMatch['_route'];
        }
        $routeIds[] = self::GLOBAL_ROUTING_KEY;

        // check until match or we run out of routeIds
        $settingsService = $this->container->get('settingsService');
        $layoutName = $settingsService->exists('zenmagick.http.view.defaultLayout') ? $settingsService->get('zenmagick.http.view.defaultLayout') : null;
        foreach ($routeIds as $routeId) {
            if (null != ($route = $this->getRouteForId($routeId))) {
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

        // TODO: enable once we have all current url mappings converted
        if (false && !$view) {
            // use conventions and defaults
            $templateName = sprintf('views/%s%s', $request->getRequestId(), $settingsService->get('zenmagick.http.templates.ext', '.php'));
            $view = Beans::getBean('defaultView');
            $view->setTemplate($templateName);
            $view->setLayout($layoutName);
        }

        if (!$view) {
            $view = \ZMUrlManager::instance()->findView($request->getRequestId(), $viewId);
        }

        if ($view instanceof TemplateView && $data) {
            $view->setVariables($data);
        }

        return $view;
    }

    /**
     * Add route.
     *
     * @param string routeId The route id.
     * @param Route route The route.
     */
    public function addRoute($routeId, Route $route) {
        $routeCollection = new RouteCollection();
        $routeCollection->add('routeId', $route);
        $this->getRouter()->getRouteCollection()->addCollection($routeCollection);
    }

    /**
     * Add routes.
     *
     * @param array routeList List of arrays containing routeId/route.
     */
    public function addRoutes(array $routeList) {
        $routeCollection = new RouteCollection();
        foreach ($routeList as $routeDetails) {
            $routeCollection->add($routeDetails[0], $routeDetails[1]);
        }
        $this->getRouter()->getRouteCollection()->addCollection($routeCollection);
    }

}
