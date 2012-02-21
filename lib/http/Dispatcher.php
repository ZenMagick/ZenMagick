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

use ReflectionClass;
use ZMRequest;
use zenmagick\base\Beans;
use zenmagick\base\Runtime;
use zenmagick\base\ZMException;
use zenmagick\base\ZMObject;
use zenmagick\base\events\Event;
use zenmagick\base\logging\Logging;
use zenmagick\base\utils\Executor;
use zenmagick\base\utils\ParameterMapper;

/**
 * ZenMagick MVC request dispatcher.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class Dispatcher extends ZMObject implements ParameterMapper {

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

        $eventDispatcher->dispatch('dispatch_start', new Event(null, array('request' => $request)));
        $view = $this->handleRequest($request);
        $eventDispatcher->dispatch('dispatch_done', new Event(null, array('request' => $request)));

        // allow plugins and event subscribers to filter/modify the final contents; corresponds with ob_start() in init.php
        $event = new Event(null, array('request' => $request, 'view' => $view, 'content' => ob_get_clean()));
        $eventDispatcher->dispatch('finalise_content', $event);

        echo $event->get('content');

        // if we get to here all messages have been displayed
        $messageService->clear();
        $messageService->saveMessages($request->getSession());

        // all done
        $eventDispatcher->dispatch('all_done', new Event(null, array('request' => $request, 'view' => $view, 'content' => $event->get('content'))));
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
            $view = $this->executeController($request);
        } catch (Exception $e) {
            $this->container->get('loggingService')->dump($e, sprintf('controller::process failed: %s', $e->getMessage()), Logging::ERROR);
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
     * {@inheritDoc}
     */
    public function mapParameter($callback, array $parameter) {
        if (!is_array($callback)) {
            return $parameter;
        }

        $rc = new ReflectionClass($callback[0]);
        $method = $rc->getMethod($callback[1]);
        // we always put that in
        $request = $parameter['request'];
        $mapped = array();
        foreach ($method->getParameters() as $rp) {
            $value = null;
            if (array_key_exists($rp->name, $parameter)) {
                $value = $parameter[$rp->name];
            } else {
                // check for known types
                $hintClass = $rp->getClass();
                if ($hintClass) {
                    // check for special classes/interfaces
                    // TODO: this is expected to grow a bit, so make the code look nicer
                    if ('zenmagick\http\forms\Form' == $hintClass->name || $hintClass->isSubclassOf('zenmagick\http\forms\Form')) {
                        $value = Beans::getBean($hintClass->name);
                        $value->populate($request);
                    } else if ('ZMRequest' == $hintClass->name || $hintClass->isSubclassOf('ZMRequest')) {
                        $value = $request;
                    } else if ('zenmagick\http\messages\Messages' == $hintClass->name || $hintClass->isSubclassOf('zenmagick\http\messages\Messages')) {
                        $value = $this->container->get('messageService');
                    } else {
                        // last choice - assume a model class that does not extend/implement FormData
                        $value = Beans::getBean($hintClass->name);
                        Beans::setAll($value, $request->getParameterMap(), null);
                    }
                }
            }
            $mapped[] = $value;
        }
        return $mapped;
    }

    /**
     * Get an executor for an controller to handle the given request.
     *
     * @param ZMRequest request The request.
     * @return Executor The executor.
     */
    public function getControllerExecutor(ZMRequest $request) {
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
                return new Executor(array(Beans::getBean($token[0]), $token[1]), $routerMatch, $this);
            }
        } else {
            $controller = \ZMUrlManager::instance()->findController($request->getRequestId());
            return new Executor(array($controller, 'process'), array($request));
        }
    }

    /**
     * Execute controller.
     *
     * @param ZMRequest request The request.
     * @return View The result view.
     */
    protected function executeController(ZMRequest $request) {
        // check authorization
        $sacsManager = $this->container->get('sacsManager');
        $sacsManager->authorize($request, $request->getRequestId(), $request->getUser());

        $settingsService = Runtime::getSettings();
        $enableTransactions = $settingsService->get('zenmagick.http.transactions.enabled', false);

        if ($enableTransactions) {
            ZMRuntime::getDatabase()->beginTransaction();
        }

        $controller = null;
        $eventDispatcher = $this->container->get('eventDispatcher');
        $eventDispatcher->dispatch('controller_process_start', new Event($this, array('request' => $request, 'controller' => $controller)));

        try {
            // execute
            $view = $this->getControllerExecutor($request)->execute();
        } catch (Exception $e) {
            if ($enableTransactions) {
                ZMRuntime::getDatabase()->rollback();
            }
            // re-throw
            throw $e;
        }

        $eventDispatcher->dispatch('controller_process_end', new Event($this, array('request' => $request, 'controller' => $controller, 'view' => $view)));

        if ($enableTransactions) {
            ZMRuntime::getDatabase()->commit();
        }

        return $view;
    }

}
