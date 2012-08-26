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
namespace zenmagick\http;

use Exception;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

use zenmagick\base\Beans;
use zenmagick\base\Runtime;
use zenmagick\base\ZMException;
use zenmagick\base\ZMObject;
use zenmagick\base\events\Event;
use zenmagick\base\logging\Logging;
use zenmagick\base\utils\Executor;
use zenmagick\base\utils\ParameterMapper;
use zenmagick\http\Request;
use zenmagick\http\session\SessionValidator;
use zenmagick\http\view\ModelAndView;
use zenmagick\http\view\ResponseModelAndView;
use zenmagick\http\view\View;

/**
 * ZenMagick MVC request dispatcher.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class HttpListener extends ZMObject {
    protected $container;
    protected $dispatcher;
    private $parameterMapper;

    public function __construct(EventDispatcherInterface $dispatcher, ContainerInterface $container) {
        $this->dispatcher = $dispatcher;
        $this->container = $container;
    }

    /**
     * Set the parameter mapper for controller.
     *
     * @param ParameterMapper parameterMapper The parameter mapper.
     */
    public function setParameterMapper(ParameterMapper $parameterMapper) {
        $this->parameterMapper = $parameterMapper;
    }

    /*
     * Handle web request.
     */
    public function onKernelRequest(GetResponseEvent $event) {
        $request = $event->getRequest();
        $request->setContainer($this->container);

        $dispatcher = $this->dispatcher;
        $dispatcher->dispatch('request_ready', new Event($this, array('request' => $request)));
        $dispatcher->dispatch('container_ready', new Event($this, array('request' => $request)));

        // allow seo rewriters to fiddle with the request
        foreach (array_reverse($this->container->get('containerTagService')->findTaggedServiceIds('zenmagick.http.request.rewriter')) as $id => $args) {
            if ($this->container->get($id)->decode($request)) break;
        }

        // make sure we use the appropriate protocol (HTTPS, for example) if required
        $this->container->get('sacsManager')->ensureAccessMethod($request);

        $messageService = $this->container->get('messageService');

        // load saved messages
        $messageService->loadMessages($request->getSession());

        $dispatcher->dispatch('dispatch_start', new Event($this, array('request' => $request)));
        ob_start();
        list($response, $view) = $this->handleRequest($request);
        $content = ob_get_clean();
        if (!empty($content) && !$view) {
            $response->setContent($content);
        }
        $dispatcher->dispatch('dispatch_done', new Event($this, array('request' => $request)));

        // ensure we do have a view if we got this far
        $view = null !== $view ? $view : $this->container->get('defaultView');
        // allow plugins and event subscribers to filter/modify the final contents; corresponds with ob_start() in init.php
        $zmevent = new Event($this, array('request' => $request, 'view' => $view, 'content' => $response->getContent()));
        $dispatcher->dispatch('finalise_content', $zmevent);

        $response->setContent($zmevent->get('content'));

        // if we get to here all messages have been displayed
        $messageService->clear();
        $messageService->saveMessages($request->getSession());

        // all done
        // @todo CHECKME: how late does this have to be?
        $dispatcher->dispatch('all_done', new Event($this, array('request' => $request, 'view' => $view, 'content' => $zmevent->get('content'))));

        $event->setResponse($response);
    }

    /**
     * Handle a request.
     *
     * @param zenmagick\http\Request request The request to dispatch.
     * @return Response The response or <code>null</code>.
     */
    public function handleRequest($request) {
        $dispatcher = $this->dispatcher;

        $view = null;
        $response = null;
        $content = '';
        try {
            // check authorization
            $sacsManager = $this->container->get('sacsManager');
            $sacsManager->authorize($request, $request->getRequestId(), $request->getAccount());

            $result = null;

            // validate session
            foreach ($this->container->get('containerTagService')->findTaggedServiceIds('zenmagick.http.session.validator') as $id => $args) {
                if (null != ($validator = $this->container->get($id)) && $validator instanceof SessionValidator) {
                    if (!$validator->isValidSession($request, $request->getSession())) {
                        $this->container->get('messageService')->error('Invalid session');
                        $request->getSession()->migrate();
                        $result = '';
                    }
                }
            }
            if (null === $result) {
                $result = $this->executeController($request);
            }
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
            try {
                $dispatcher->dispatch('view_start', new Event(null, array('request' => $request, 'view' => $view)));
                // generate response
                $content = $view->generate($request);
                $dispatcher->dispatch('view_done', new Event(null, array('request' => $request, 'view' => $view)));
            } catch (ZMException $e) {
            } catch (Exception $e) {
                //TODO: what to do?
            }
        } else {
            $this->container->get('logger')->debug('null view, skipping $view->generate()');
        }

        // convert view stuff to response...
        $response = $response ?: new Response($content);
        return array($response, $view);
    }

    /**
     * Execute controller.
     *
     * @param zenmagick\http\Request request The request.
     * @return mixed The result.
     */
    protected function executeController(Request $request) {
        $controller = null;
        $dispatcher = $this->dispatcher;

        try {
            // @todo move this to the  onKernelController event.
            if (Runtime::isContextMatch('storefront')) {
                if ($this->container->get('themeService')->getActiveTheme()->getMeta('zencart')) {
                    $settingsService = $this->container->get('settingsService');
                    $settingsService->set('zenmagick.http.view.defaultLayout', null);
                    $executor = new Executor(array(Beans::getBean('zenmagick\apps\store\bundles\ZenCartBundle\controller\ZencartStorefrontController'), 'process'), array($request));
                    return $executor->execute();
                }
            }

            if ($routerMatch = $this->container->get('routeResolver')->getRouterMatch($request->getRequestUri())) {
                // class:method ?
                $token = explode(':', $routerMatch['_controller']);
                if (1 == count($token)) {
                    // traditional controller
                    $controller = Beans::getBean($routerMatch['_controller']);
                    $executor = new Executor(array($controller, 'process'), array($request));
                } else {
                    // wrap to allow custom method with variable parameter
                    // TODO: remove once all controller use type hints for $request
                    if (!array_key_exists('request', $routerMatch)) {
                        // allow $request as mappable parameter too
                        $routerMatch['request'] = $request;
                    }
                    $executor =  new Executor(array(Beans::getBean($token[0]), $token[1]), $routerMatch, $this->parameterMapper);
                }
            } else {
                //TODO: default controller
                $controller = $this->container->get('urlManager')->findController($request->getRequestId());
                $executor = new Executor(array($controller, 'process'), array($request));
            }

            $result = $executor->execute();
        } catch (Exception $e) {
        echo $e->getMessage();
        echo $e->getTraceAsString();
        die();
            // re-throw
            throw $e;
        }

        return $result;
    }
}
