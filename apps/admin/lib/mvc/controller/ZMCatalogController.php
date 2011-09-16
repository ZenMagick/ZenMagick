<?php
/*
 * ZenMagick - Smart e-commerce
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

use zenmagick\base\Beans;
use zenmagick\base\Runtime;
use zenmagick\base\ClassLoader;
use zenmagick\http\sacs\SacsManager;

/**
 * Admin controller for catalog page(s).
 *
 * <p>This controller acts as proxy for the actual controller. The actual controller is defined by the <em>catalogRequestId</em> parameter.</p>
 *
 * @author DerManoMann
 * @package zenmagick.store.admin.mvc.controller
 */
class ZMCatalogController extends ZMController {

    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Create list of all active catalog content controllers.
     *
     * @param ZMRequest request The current request.
     * @return array List of controller instances.
     */
    protected function getCatalogContentControllers($request) {
        $controllers = array();
        // find first active controller and pick
        foreach (explode(',', ZMSettings::get('apps.store.catalog.controller')) as $bean) {
            if (null != ($controller = Beans::getBean(trim($bean))) && $controller instanceof ZMCatalogContentController) {
                if ($controller->isActive($request)) {
                    $controllers[] = $controller;
                }
            }
        }

        return $controllers;
    }

    /**
     * {@inheritDoc}
     */
    public function process($request) {
        // disable POST in demo
        if ('POST' == $request->getMethod() && $request->handleDemo()) {
            return $this->findView('success-demo');
        }

        $controllers = $this->getCatalogContentControllers($request);
        $controller = null;
        if (null == ($catalogRequestId = $request->getParameter('catalogRequestId'))) {
            if (0 < count($controllers)) {
                $controller = $controllers[0];
                $catalogRequestId = $controller->getCatalogRequestId();
                Runtime::getLogging()->log('defaulting to controller : '.get_class($controller), ZMLogging::DEBUG);
            }
        } else {
            // let's see if we have a controller for this...
            $definition = ClassLoader::className($catalogRequestId.'Controller');
            $controller = Beans::getBean($definition);
            Runtime::getLogging()->log('delegating to controller : '.get_class($controller), ZMLogging::DEBUG);

        }

        // check authorization as we'll need the follwo up redirect point to the catalog URL, not a tab url
        $authorized = $this->container->get('sacsManager')->authorize($request, $request->getRequestId(), $request->getUser(), false);

        if (null == $controller || !$authorized) {
            // no controller found
            return parent::process($request);
        }

        // fake requestId
        $requestId = $request->getRequestId();
        $request->setRequestId($catalogRequestId);

        // process
        $catalogViewContent = null;
        try {
            $catalogContentView = $controller->process($request);
            $catalogContentView->setLayout(null);
            $catalogViewContent = $catalogContentView->generate($request);
        } catch (Exception $e) {
            Runtime::getLogging()->dump($e, 'view::generate failed', ZMLogging::ERROR);
            $catalogViewContent = null;
        }

        // restore for normal processing
        $request->setRequestId($requestId);

        // now do the normal thing
        $view = parent::process($request);

        // add catalog content view to be used in catalog view template
        $view->setVar('catalogRequestId', $catalogRequestId);
        $view->setVar('catalogViewContent', $catalogViewContent);
        $view->setVar('controllers', $controllers);

        return $view;
    }

}
