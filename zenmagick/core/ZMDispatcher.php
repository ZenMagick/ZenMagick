<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2009 ZenMagick
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


/**
 * ZenMagick dispatcher.
 *
 * @author DerManoMann
 * @package org.zenmagick
 */
class ZMDispatcher {

    /**
     * Dispatch a request.
     */
    public static function dispatch() {
        // pick up messages from zen-cart request handling
        ZMMessages::instance()->_loadMessageStack();

        // main request processor
        if (ZMSettings::get('isEnableZMThemes')) {

            ZMEvents::instance()->fireEvent(null, ZMEvents::DISPATCH_START);
            self::handleRequest();
            ZMEvents::instance()->fireEvent(null, ZMEvents::DISPATCH_DONE);

            // allow plugins and event subscribers to filter/modify the final contents
            $args = ZMEvents::instance()->fireEvent(null, ZMEvents::FINALISE_CONTENTS, array('contents' => ob_get_clean()));
            echo $args['contents'];

            // clear messages if not redirect...
            ZMRequest::getSession()->clearMessages();

            ZMEvents::instance()->fireEvent(null, ZMEvents::ALL_DONE);

            ZMRuntime::finish();
        }
    }

    /**
     * Handle a request.
     */
    public static function handleRequest() {
        $controller = ZMUrlMapper::instance()->findController(ZMRequest::getPageName());
        ZMRequest::setController($controller);

        try {
            // execute controller
            $view = $controller->process();
        } catch (Exception $e) {
            ZMLogging::instance()->dump($e, null, ZMLogging::WARN);

            // TODO: extract somewhere into method/function??
            $controller = ZMLoader::make(ZMSettings::get('defaultControllerClass'));
            $controller->exportGlobal('exception', $e);
            $view = $controller->findView('error');
            // uguu!
            $view->setController($controller);
            $controller->setView($view);
            ZMRequest::setController($controller);
        }

        // generate response
        if (null != $view) {
            header('Content-Type: '.$view->getContentType().'; charset='.$view->getEncoding());
            ZMEvents::instance()->fireEvent(null, ZMEvents::VIEW_START, array('view' => $view));
            try {
                $view->generate();
            } catch (Exception $e) {
                ZMLogging::instance()->dump($e, null, ZMLogging::WARN);
                //TODO: what to do?
            } 
            ZMEvents::instance()->fireEvent(null, ZMEvents::VIEW_DONE, array('view' => $view));
        } else {
            ZMLogging::instance()->log('null view, skipping $view->generate()', ZMLogging::DEBUG);
        }
    }

}

?>
