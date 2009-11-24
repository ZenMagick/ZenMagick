<?php
/*
 * ZenMagick - Another PHP framework.
 * Copyright (C) 2006,2009 ZenMagick
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
        ZMEvents::instance()->fireEvent(null, ZMMVCConstants::DISPATCH_START, array('request' => $request));
        self::handleRequest($request);
        ZMEvents::instance()->fireEvent(null, ZMMVCConstants::DISPATCH_DONE, array('request' => $request));

        // allow plugins and event subscribers to filter/modify the final contents
        $args = ZMEvents::instance()->fireEvent(null, ZMMVCConstants::FINALISE_CONTENTS, array('request' => $request, 'contents' => ob_get_clean()));
        echo $args['contents'];

        ZMEvents::instance()->fireEvent(null, ZMMVCConstants::ALL_DONE, array('request' => $request));
    }

    /**
     * Handle a request.
     *
     * @param ZMRequest request The request to dispatch.
     */
    public static function handleRequest($request) {
        $controller = $request->getController();

        try {
            // execute controller
            $view = $controller->process($request);
        } catch (Exception $e) {
            ZMLogging::instance()->dump($e, null, ZMLogging::ERROR);
            $controller = ZMLoader::make(ZMSettings::get('zenmagick.mvc.controller.defaultClass', 'DefaultController'));
            $view = $controller->findView('error', array('exception' => $e));
            $request->setController($controller);
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

            ZMEvents::instance()->fireEvent(null, ZMMVCConstants::VIEW_START, array('request' => $request, 'view' => $view));

            try {
                // generate response
                echo $view->generate($request);
            } catch (Exception $e) {
                ZMLogging::instance()->dump($e, null, ZMLogging::ERROR);
                //TODO: what to do?
            } 
            ZMEvents::instance()->fireEvent(null, ZMMVCConstants::VIEW_DONE, array('request' => $request, 'view' => $view));
        } else {
            ZMLogging::instance()->log('null view, skipping $view->generate()', ZMLogging::DEBUG);
        }
    }

}

?>
