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
namespace zenmagick\http;

use Exception;
use ZMRequest;
use zenmagick\base\Beans;
use zenmagick\base\Runtime;
use zenmagick\base\ZMException;
use zenmagick\base\ZMObject;
use zenmagick\base\events\Event;
use zenmagick\base\logging\Logging;
use zenmagick\base\utils\Executor;
use zenmagick\base\utils\ParameterMapper;
use zenmagick\http\view\ModelAndView;
use zenmagick\http\view\View;

/**
 * ZenMagick MVC request dispatcher.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class Dispatcher extends ZMObject {
    private $parameterMapper;

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
     * @param ZMRequest request The request to dispatch.
     */
    public function dispatch($request) {
        ob_start();

        $messageService = $this->container->get('messageService');
        $eventDispatcher = $this->container->get('eventDispatcher');

        // load saved messages
        $messageService->loadMessages($request->getSession());

        $eventDispatcher->dispatch('dispatch_start', new Event($this, array('request' => $request)));
        $view = $this->handleRequest($request);
        $eventDispatcher->dispatch('dispatch_done', new Event($this, array('request' => $request)));

        // allow plugins and event subscribers to filter/modify the final contents; corresponds with ob_start() in init.php
        $event = new Event($this, array('request' => $request, 'view' => $view, 'content' => ob_get_clean()));
        $eventDispatcher->dispatch('finalise_content', $event);

        echo $event->get('content');

        // if we get to here all messages have been displayed
        $messageService->clear();
        $messageService->saveMessages($request->getSession());

        // all done
        $eventDispatcher->dispatch('all_done', new Event($this, array('request' => $request, 'view' => $view, 'content' => $event->get('content'))));
        $request->closeSession();
    }

    /**
     * Handle a request.
     *
     * @param ZMRequest request The request to dispatch.
     * @return View The view or <code>null</code>.
     */
    public function handleRequest($request) {
        $eventDispatcher = $this->container->get('eventDispatcher');

        try {
            $result = $this->executeController($request);

            // make sure we end up with a View instance
            $view = null;
            $routeResolver = $this->container->get('routeResolver');
            if (is_string($result)) {
                $view = $routeResolver->getViewForId($result, $request);
            } else if ($result instanceof ModelAndView) {
                $view = $routeResolver->getViewForId($result->getViewId(), $request);
                $view->setVariables($result->getModel());
            } else if ($result instanceof View) {
                $view = $result;
            }
        } catch (Exception $e) {
            $this->container->get('loggingService')->dump($e, sprintf('controller::process failed: %s', $e->getMessage()), Logging::ERROR);
            //TODO: why is this a classic controller only?
            $controller = $this->container->get('defaultController');
            $view = $controller->findView('error', array('exception' => $e));
            $request->setController($controller);
            $controller->initViewVars($view, $request);
        }

        // generate response
        if (null != $view) {
            try {
                if (null !== $view->getContentType()) {
                    $s = 'Content-Type: '.$view->getContentType();
                    if (null !== $view->getEncoding()) {
                        $s .= '; charset='.$view->getEncoding();
                    }
                    header($s);
                }
                $eventDispatcher->dispatch('view_start', new Event(null, array('request' => $request, 'view' => $view)));
                // generate response
                echo $view->generate($request);
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

        return $view;
    }

    /**
     * Execute controller.
     *
     * @param ZMRequest request The request.
     * @return mixed The result;
     */
    protected function executeController(ZMRequest $request) {
        // check authorization
        $sacsManager = $this->container->get('sacsManager');
        $sacsManager->authorize($request, $request->getRequestId(), $request->getUser());

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
     * Get an executor for an controller to handle the given request.
     *
     * @param ZMRequest request The request.
     * @return Executor The executor.
     */
    protected function getControllerExecutor(ZMRequest $request) {
        if ($routerMatch = $this->container->get('routeResolver')->getRouterMatch($request->getUri())) {
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
            $controller = \ZMUrlManager::instance()->findController($request->getRequestId());
            return new Executor(array($controller, 'process'), array($request));
        }
    }

}
