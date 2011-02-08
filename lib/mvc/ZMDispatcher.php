<?php
/*
 * ZenMagick - Another PHP framework.
 * Copyright (C) 2006-2010 zenmagick.org
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

use zenmagick\base\Runtime;
use zenmagick\base\events\Event;


/**
 * ZenMagick MVC request dispatcher.
 *
 * @author DerManoMann
 * @package org.zenmagick.mvc
 */
class ZMDispatcher {

    /**
     * Dispatch a request.
     *
     * @param ZMRequest request The request to dispatch.
     */
    public static function dispatch($request) {
        ob_start();

        // load saved messages
        \ZMMessages::instance()->loadMessages($request->getSession());

        Runtime::getEventDispatcher()->notify(new Event(null, 'dispatch_start', array('request' => $request)));
        $view = self::handleRequest($request);
        Runtime::getEventDispatcher()->notify(new Event(null, 'dispatch_done', array('request' => $request)));

        // allow plugins and event subscribers to filter/modify the final contents; corresponds with ob_start() in init.php
        $contents = Runtime::getEventDispatcher()->filter(new Event(null, 'finalise_contents', array('request' => $request, 'view' => $view)), ob_get_clean());
        echo $contents;

        // all done
        Runtime::getEventDispatcher()->notify(new Event(null, 'all_done',  array('request' => $request, 'view' => $view, 'contents' => $contents)));
    }

    /**
     * Handle a request.
     *
     * @param ZMRequest request The request to dispatch.
     * @return ZMView The view or <code>null</code>.
     */
    public static function handleRequest($request) {
        $controller = $request->getController();
        $view = null;

        try {
            // execute controller
            $view = $controller->process($request);
        } catch (Exception $e) {
            \ZMLogging::instance()->dump($e, 'controller::process failed', \ZMLogging::ERROR);
            $controller = \ZMBeanUtils::getBean(\ZMSettings::get('zenmagick.mvc.controller.default', 'Controller'));
            $view = $controller->findView('error', array('exception' => $e));
            $request->setController($controller);
            $controller->initViewVars($view, $request);
        }

        // generate response
        if (null != $view) {
            if (null !== $view->getContentType()) {
                $s = 'Content-Type: '.$view->getContentType();
                if (null !== $view->getEncoding()) {
                    $s .= '; charset='.$view->getEncoding();
                }
                header($s);
            }

            Runtime::getEventDispatcher()->notify(new Event(null, 'view_start',  array('request' => $request, 'view' => $view)));
            try {
                // generate response
                echo $view->generate($request);
            } catch (Exception $e) {
                \ZMLogging::instance()->dump($e, 'view::generate failed', \ZMLogging::ERROR);
                //TODO: what to do?
            }
            Runtime::getEventDispatcher()->notify(new Event(null, 'view_done',  array('request' => $request, 'view' => $view)));
        } else {
            \ZMLogging::instance()->log('null view, skipping $view->generate()', \ZMLogging::DEBUG);
        }

        return $view;
    }

}
