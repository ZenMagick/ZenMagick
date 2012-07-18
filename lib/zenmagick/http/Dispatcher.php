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

use zenmagick\base\Beans;
use zenmagick\base\Runtime;
use zenmagick\base\Toolbox;
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
class Dispatcher extends ZMObject implements HttpKernelInterface {
    private $parameterMapper;
    private $controllerExecutor;

    /*
     * Handle web request.
     */
    public function handle(\Symfony\Component\HttpFoundation\Request $request, $type = self::MASTER_REQUEST, $catch = true) {
        try {
            $container = $this->container;
            $kernel = $container->get('kernel');
            $settingsService = $container->get('settingsService');
            $request = $container->get('request'); // @todo use it from the argument :)
            // allow seo rewriters to fiddle with the request
            foreach ($request->getUrlRewriter() as $rewriter) {
                if ($rewriter->decode($request)) break; // traditional ZenMagick routing
            }

            // make sure we use the appropriate protocol (HTTPS, for example) if required
            $container->get('sacsManager')->ensureAccessMethod($request);

            // form validation
            $validationConfig = $kernel->getApplicationPath().'/config/validation.yaml';
            if ($container->has('validator') && file_exists($validationConfig)) {
                $container->get('validator')->load(file_get_contents(Toolbox::resolveWithEnv($validationConfig)));
            }

            // reset as other global code migth fiddle with it...
            $kernel->fireEvent('init_done', array('request' => $request));
            return $this->dispatch($request);
        } catch (Exception $e) {
            if (false === $catch) {
                throw $e;
            }
            return new Response(sprintf('serve failed: %s', $e->getMessage()), 500);
        }
    }

    /**
     * Set the parameter mapper for controller.
     *
     * @param ParameterMapper parameterMapper The parameter mapper.
     */
    public function setParameterMapper(ParameterMapper $parameterMapper) {
        $this->parameterMapper = $parameterMapper;
    }

    /**
     * Dispatch a request.
     *
     * @param zenmagick\http\Request request The request to dispatch.
     */
    public function dispatch($request) {
        $request->setDispatcher($this);

        $messageService = $this->container->get('messageService');
        $eventDispatcher = $this->container->get('eventDispatcher');

        // load saved messages
        $messageService->loadMessages($request->getSession());

        $eventDispatcher->dispatch('dispatch_start', new Event($this, array('request' => $request)));
        ob_start();
        list($response, $view) = $this->handleRequest($request);
        $content = ob_get_clean();
        if (!empty($content) && !$view) {
            $response->setContent($content);
        }
        $eventDispatcher->dispatch('dispatch_done', new Event($this, array('request' => $request)));

        // ensure we do have a view if we got this far
        $view = null !== $view ? $view : $this->container->get('defaultView');
        // allow plugins and event subscribers to filter/modify the final contents; corresponds with ob_start() in init.php
        $event = new Event($this, array('request' => $request, 'view' => $view, 'content' => $response->getContent()));
        $eventDispatcher->dispatch('finalise_content', $event);

        $response->setContent($event->get('content'));

        // if we get to here all messages have been displayed
        $messageService->clear();
        $messageService->saveMessages($request->getSession());

        // all done
        // @todo CHECKME: how late does this have to be?
        $eventDispatcher->dispatch('all_done', new Event($this, array('request' => $request, 'view' => $view, 'content' => $event->get('content'))));
        $request->closeSession();

        return $response;
    }

    /**
     * Handle a request.
     *
     * @param zenmagick\http\Request request The request to dispatch.
     * @return Response The response or <code>null</code>.
     */
    public function handleRequest($request) {
        $eventDispatcher = $this->container->get('eventDispatcher');

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
                        $this->container->get('loggingService')->trace(sprintf('session validation failed %s', $validator));
                        $this->container->get('messageService')->error('Invalid session');
                        $request->getSession()->regenerate();
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
            $this->container->get('loggingService')->dump($e, sprintf('controller::process failed: %s', $e->getMessage()), Logging::ERROR);
            //TODO: why is this a classic controller only?
            $controller = $this->container->get('defaultController');
            $view = $controller->findView('error', array('exception' => $e));
            $controller->initViewVars($view, $request);
        }

        // populate response
        if (null != $view) {
            try {
                $eventDispatcher->dispatch('view_start', new Event(null, array('request' => $request, 'view' => $view)));
                // generate response
                $content = $view->generate($request);
                $eventDispatcher->dispatch('view_done', new Event(null, array('request' => $request, 'view' => $view)));
            } catch (ZMException $e) {
                $this->container->get('loggingService')->dump($e, sprintf('view::generate failed: %s', $e), Logging::ERROR);
            } catch (Exception $e) {
                $this->container->get('loggingService')->dump($e, sprintf('view::generate failed: %s', $e->getMessage()), Logging::ERROR);
                //TODO: what to do?
            }
        } else {
            $this->container->get('loggingService')->debug('null view, skipping $view->generate()');
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
        $settingsService = $this->container->get('settingsService');
        $enableTransactions = $settingsService->get('zenmagick.http.transactions.enabled', false);

        if ($enableTransactions) {
            ZMRuntime::getDatabase()->beginTransaction();
        }

        $controller = null;
        $eventDispatcher = $this->container->get('eventDispatcher');
        $eventDispatcher->dispatch('controller_process_start', new Event($this, array('request' => $request, 'controller' => $controller)));

        try {
            // execute
            $executor = $this->getControllerExecutor($request);
            $result = $executor->execute();
        } catch (Exception $e) {
            if ($enableTransactions) {
                ZMRuntime::getDatabase()->rollback();
            }
            // re-throw
            throw $e;
        }

        $eventDispatcher->dispatch('controller_process_end', new Event($this, array('request' => $request, 'controller' => $controller, 'result' => $result)));

        if ($enableTransactions) {
            ZMRuntime::getDatabase()->commit();
        }

        return $result;
    }

    /**
     * Set controller executor.
     *
     * <p>Allows to override the dynamical controller executor lookup.
     *
     * @param Executor executor Executor.
     */
    public function setControllerExecutor(Executor $executor) {
        $this->controllerExecutor = $executor;
    }

    /**
     * Get an executor for an controller to handle the given request.
     *
     * @param zenmagick\http\Request request The request.
     * @return Executor The executor.
     */
    protected function getControllerExecutor(Request $request) {
        if ($this->controllerExecutor) {
            return $this->controllerExecutor;
        }

        if ($routerMatch = $this->container->get('routeResolver')->getRouterMatch($request->getRequestUri())) {
            // class:method ?
            $token = explode(':', $routerMatch['_controller']);
            if (1 == count($token)) {
                // traditional controller
                $controller = Beans::getBean($routerMatch['_controller']);
                return new Executor(array($controller, 'process'), array($request));
            } else {
                // wrap to allow custom method with variable parameter
                // TODO: remove once all controller use type hints for $request
                if (!array_key_exists('request', $routerMatch)) {
                    // allow $request as mappable parameter too
                    $routerMatch['request'] = $request;
                }
                return new Executor(array(Beans::getBean($token[0]), $token[1]), $routerMatch, $this->parameterMapper);
            }
        } else {
            //TODO: default controller
            $controller = \ZMUrlManager::instance()->findController($request->getRequestId());
            return new Executor(array($controller, 'process'), array($request));
        }
    }

}
