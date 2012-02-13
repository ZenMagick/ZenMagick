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
namespace zenmagick\http\routing;

use Exception;
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
        if (!Runtime::getSettings()->get('zenmagick.http.routing.enabled', true)) {
            return null;
        }

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
        if (!Runtime::getSettings()->get('zenmagick.http.routing.enabled', true)) {
            return null;
        }
        return $this->getRouter()->getRouteCollection()->get($routeId);
    }

    /**
     * Get a controller instance for the given request.
     *
     * @return ZMController A controller instance or <code>null</code>.
     */
    public function getControllerForRequest($request) {
        $controller = null;
        if ($routerMatch = $this->getRouterMatch($request->getUri())) {
            // class:method ?
            $token = explode(':', $routerMatch['_controller']);
            if (1 == count($token)) {
                // expect a ZMController instance with traditional processing
                $controller = Beans::getBean($routerMatch['_controller']);
            } else {
                // wrap to allow custom method with variable parameters
                // TODO: remove once all controller use type hints for $request
                if (!array_key_exists('request', $routerMatch)) {
                    // allow $request as mappable parameter too
                    $routerMatch['request'] = $request;
                }
                $controller_ = new \ZMRoutingController(Beans::getBean($token[0]), $token[1], $routerMatch);
                $controller_->setContainer($this->container);
            }
        } else {
            $controller = \ZMUrlManager::instance()->findController($request->getRequestId());
        }

        return $controller;
    }

    /**
     * Get a view for the given request and view id.
     *
     * @param string viewId The view id.
     * @param ZMRequest request The current request.
     * @param array Optional view data; default is an empty array.
     * @return View A view.
     */
    public function getViewForId($viewId, $request, array $data=array()) {
        $view = null;
        $routeId = null;
        if (null != ($routerMatch = $this->getRouterMatch($request->getUri()))) {
            $routeId = $routerMatch['_route'];
        } else {
            $routeId = self::GLOBAL_ROUTING_KEY;
        }
        if (null != ($route = $this->getRouteForId($routeId))) {
            $viewKey = null == $viewId ? 'view' : sprintf('view:%s', $viewId);
            $options = $route->getOptions();
            if (array_key_exists($viewKey, $options)) {
                $viewDefinition = null;
                $token = parse_url($options[$viewKey]);
                if (array_key_exists('scheme', $token)) {
                    $viewDefinition = sprintf('%s#requestId=%s&%s', $token['scheme'], $token['host'], $token['query']);
                } else {
                    $viewDefinition = sprintf('%s#template=%s&%s', 'defaultView', $token['path'], $token['query']);
                }
                $view = Beans::getBean($viewDefinition);
            }
        }

        // TODO: enable
        if (false && !$view) {
            // use conventions to default the template name
            $settingsService = $this->container->get('settingsService');
            $templateName = sprintf('views/%s%s', $request->getRequestId(), $settingsService->get('zenmagick.http.templates.ext', '.php'));
            $layoutName = $settingsService->exists('zenmagick.mvc.view.defaultLayout') ? $settingsService->get('zenmagick.mvc.view.defaultLayout') : null;
            $view = Beans::getBean('defaultView');
            $view->setTemplate($templateName);
            $view->setLayout($layoutName);
        }

        if (!$view) {
            // legacy url mappings
            $view = \ZMUrlManager::instance()->findView($request->getRequestId(), $viewId);
        }

        if ($view instanceof TemplateView && $data) {
            $view->setVariables($data);
        }

        return $view;
    }

}
