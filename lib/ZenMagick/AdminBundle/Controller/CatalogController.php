<?php
/*
 * ZenMagick - Smart e-commerce
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
namespace ZenMagick\AdminBundle\Controller;

use ZenMagick\Base\Beans;
use ZenMagick\Base\Runtime;
use ZenMagick\Base\Toolbox;
use ZenMagick\Http\Request;
use ZenMagick\Http\Sacs\SacsManager;

/**
 * Admin controller for catalog page(s).
 *
 * <p>This controller acts as proxy for the actual controller. The actual controller is defined by the <em>catalogRequestId</em> parameter.</p>
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class CatalogController extends \ZMController
{
    /**
     * Create list of all active catalog content controllers.
     *
     * @param ZenMagick\Http\Request request The current request.
     * @return array List of controller instances.
     */
    protected function getCatalogContentControllers($request)
    {
        $controllers = array();
        foreach ($this->container->get('containerTagService')->findTaggedServiceIds('apps.store.admin.tabcontroller') as $id => $args) {
            $controller = $this->container->get($id);
            if ($controller->isActive($request)) {
                $controllers[] = $controller;
            }
        }

        return $controllers;
    }

    /**
     * {@inheritDoc}
     */
    public function processAction(Request $request)
    {
        // disable POST in demo
        if ('POST' == $request->getMethod() && $this->handleDemo()) {
            return $this->findView('success-demo');
        }

        $controllers = $this->getCatalogContentControllers($request);
        $controller = null;
        if (null == ($catalogRequestId = $request->query->get('catalogRequestId'))) {
            if (0 < count($controllers)) {
                $controller = $controllers[0];
                $catalogRequestId = $controller->getCatalogRequestId();
                Runtime::getLogging()->debug('defaulting to controller : '.get_class($controller));
            }
        } else {
            // let's see if we have a controller for this...
            $definition = Toolbox::className($catalogRequestId.'Controller');
            $controller = Beans::getBean($definition);
            Runtime::getLogging()->debug('delegating to controller : '.get_class($controller));

        }

        // check authorization as we'll need the follow up redirect point to the catalog URL, not a tab url
        $authorized = $this->container->get('sacsManager')->authorize($request, $request->getRequestId(), $this->getUser(), false);

        if (null == $controller || !$authorized) {
            // no controller found
            return parent::processAction($request);
        }

        // fake requestId
        $requestId = $request->getRequestId();
        $request->setRequestId($catalogRequestId);

        // processAction
        $catalogViewContent = null;
        try {
            $catalogContentView = $controller->processAction($request);
            $catalogContentView->setLayout(null);
            $catalogViewContent = $catalogContentView->generate($request);
        } catch (Exception $e) {
            $catalogViewContent = null;
        }

        // restore for normal processing
        $request->setRequestId($requestId);

        // now do the normal thing
        $view = parent::processAction($request);

        // add catalog content view to be used in catalog view template
        $view->setVariable('catalogRequestId', $catalogRequestId);
        $view->setVariable('catalogViewContent', $catalogViewContent);
        $view->setVariable('controllers', $controllers);

        return $view;
    }

}
