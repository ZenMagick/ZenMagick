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
namespace ZenMagick\ZenMagickBundle\EventListener;

use Exception;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use ZenMagick\Base\Beans;
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

        if (HttpKernelInterface::MASTER_REQUEST !== $event->getRequestType()) return;
        $dispatcher = $event->getDispatcher();
        $request->getSession()->restorePersistedServices();

        $dispatcher->dispatch('request_ready', new GenericEvent($this, array('request' => $request)));
        $dispatcher->dispatch('container_ready', new GenericEvent($this, array('request' => $request)));

        $this->container->get('sacsManager')->ensureAccessMethod($request);
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
    }

    public function onKernelView(GetResponseForControllerResultEvent $event) {
        $request = $event->getRequest();

        $dispatcher = $event->getDispatcher();

        $controllerResult = $event->getControllerResult();
        $view = $this->getView($controllerResult);

        // ensure we do have a view if we got this far
        $view = $view ?: $this->container->get('defaultView');

        // populate response
        $response = new Response();
        $dispatcher->dispatch('view_start', new GenericEvent(null, array('request' => $request, 'view' => $view)));
        $response->setContent($view->generate($request));
        $dispatcher->dispatch('view_done', new GenericEvent(null, array('request' => $request, 'view' => $view)));

        $zmevent = new GenericEvent($this, array('request' => $request, 'view' => $view, 'content' => $response->getContent()));
        $dispatcher->dispatch('finalise_content', $zmevent);

        $response->setContent($zmevent->getArgument('content'));
        $dispatcher->dispatch('all_done', new GenericEvent($this, array('request' => $request, 'view' => $view, 'content' => $zmevent->getArgument('content'))));

        $event->setResponse($response);
    }

    public function getView($controllerResult) {
        $view = null;
        try {
            // make sure we end up with a View instance
            $routeResolver = $this->container->get('routeResolver');
            if (is_string($controllerResult)) {
                $view = $routeResolver->getViewForId($controllerResult, $request);
            } else if ($controllerResult instanceof ModelAndView) {
                $view = $routeResolver->getViewForId($controllerResult->getViewId(), $request);
                $view->setVariables($controllerResult->getModel());
            } else if ($controllerResult instanceof View) {
                $view = $controllerResult;
            }
        } catch (Exception $e) {
            //TODO: why is this a classic controller only?
            $controller = $this->container->get('defaultController');
            $view = $controller->findView('error', array('exception' => $e));
            $controller->initViewVars($view, $request);
        }

        return $view;
    }

    public static function getSubscribedEvents() {
        return array(
            KernelEvents::REQUEST => array(
                array('onKernelRequest', 14),
            ),
            KernelEvents::VIEW => array(
                array('onKernelView'),
            )
        );
    }
}
