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
namespace ZenMagick\Http;

use Exception;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use ZenMagick\Base\Beans;
use ZenMagick\Base\Events\Event;
use ZenMagick\Base\Utils\Executor;
use ZenMagick\Http\Session\SessionValidator;
use ZenMagick\Http\View\ModelAndView;
use ZenMagick\Http\View\View;

/**
 * ZenMagick MVC request dispatcher.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class HttpListener implements EventSubscriberInterface {
    protected $container;

    public function __construct(ContainerInterface $container) {
        $this->container = $container;
    }

    public function onKernelRequest(GetResponseEvent $event) {
        $request = $event->getRequest();

        // If we have a somebody elses controller, just exit (for now)
        $controller = $request->attributes->get('_controller');
        if ((false === strpos($controller, 'ZM')) && (false === strpos($controller, 'ZenMagick'))) return;


        $request->setContainer($this->container);

        if (HttpKernelInterface::MASTER_REQUEST !== $event->getRequestType()) return;
        $dispatcher = $event->getDispatcher();
        $request->getSession()->restorePersistedServices();

        $dispatcher->dispatch('request_ready', new Event($this, array('request' => $request)));
        $dispatcher->dispatch('container_ready', new Event($this, array('request' => $request)));

        // allow seo rewriters to fiddle with the request
        foreach (array_reverse($this->container->get('containerTagService')->findTaggedServiceIds('zenmagick.http.request.rewriter')) as $id => $args) {
            if ($this->container->get($id)->decode($request)) break;
        }

        $this->container->get('sacsManager')->ensureAccessMethod($request);

        $dispatcher->dispatch('dispatch_start', new Event($this, array('request' => $request)));

        $this->container->get('sacsManager')->authorize($request, $request->getRequestId(), $request->getAccount());
        foreach ($this->container->get('containerTagService')->findTaggedServiceIds('zenmagick.http.session.validator') as $id => $args) {
            if (null != ($validator = $this->container->get($id)) && $validator instanceof SessionValidator) {
                $session = $request->getSession();
                if (!$validator->isValidSession($request, $session)) {
                    $session->getFlashBag()->error('Invalid session');
                    $session->migrate();
                    $request->redirect($request->server->get('HTTP_REFERER'));
                }
            }
        }

        ob_start();
        list($response, $view) = $this->handleRequest($request, $event->getDispatcher());
        $content = ob_get_clean();
        if (!empty($content) && !$view) {
            $response->setContent($content);
        }

        // ensure we do have a view if we got this far
        $view = null !== $view ? $view : $this->container->get('defaultView');

        $zmevent = new Event($this, array('request' => $request, 'view' => $view, 'content' => $response->getContent()));
        $dispatcher->dispatch('finalise_content', $zmevent);

        $response->setContent($zmevent->get('content'));
        $dispatcher->dispatch('all_done', new Event($this, array('request' => $request, 'view' => $view, 'content' => $zmevent->get('content'))));

        $event->setResponse($response);
    }

    public function handleRequest($request, $dispatcher) {
        $response = $view = null;
        $content = '';
        try {
            $result = $this->executeController($request);
            // make sure we end up with a View instance
            $routeResolver = $this->container->get('routeResolver');
            if (is_string($result)) {
                $view = $routeResolver->getViewForId($result, $request);
            } else if ($result instanceof ModelAndView) {
                $view = $routeResolver->getViewForId($result->getViewId(), $request);
                $view->setVariables($result->getModel());
            } else if ($result instanceof View) {
                $view = $result;
            } else if ($result instanceof Response) {
                $response = $result;
            }
        } catch (Exception $e) {
            //TODO: why is this a classic controller only?
            $controller = $this->container->get('defaultController');
            $view = $controller->findView('error', array('exception' => $e));
            $controller->initViewVars($view, $request);
        }

        // populate response
        if (null != $view) {
            $dispatcher->dispatch('view_start', new Event(null, array('request' => $request, 'view' => $view)));
            $content = $view->generate($request);
            $dispatcher->dispatch('view_done', new Event(null, array('request' => $request, 'view' => $view)));
        }
        $response = $response ?: new Response($content);
        return array($response, $view);
    }

    protected function executeController(Request $request) {
        $controller = null;

        if ($routerMatch = $this->container->get('routeResolver')->getRouterMatch($request->getRequestUri())) {
            $token = explode('::', $routerMatch['_controller']); // class::method ?

            if (1 == count($token)) { // traditional controller
                $controller = Beans::getBean($routerMatch['_controller']);
                $executor = new Executor(array($controller, 'process'), array($request));
            } else {
                // wrap to allow custom method with variable parameter
                // TODO: remove once all controller use type hints for $request
                if (!array_key_exists('request', $routerMatch)) {
                    // allow $request as mappable parameter too
                    $routerMatch['request'] = $request;
                }
                $parameterMapper = $this->container->get('controllerParameterMapper');
                $executor =  new Executor(array(Beans::getBean($token[0]), $token[1]), $routerMatch, $parameterMapper);
            }
        } else {
            //TODO: default controller
            $controller = $this->container->get('urlManager')->findController($request->getRequestId());
            $executor = new Executor(array($controller, 'process'), array($request));
        }

        $result = $executor->execute();

        return $result;
    }

    public static function getSubscribedEvents() {
        return array(
            'kernel.request' => array(
                array('onKernelRequest', 15),
            )
        );
    }
}
