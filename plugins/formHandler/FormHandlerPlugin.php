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
namespace ZenMagick\plugins\formHandler;

use ZenMagick\apps\store\plugins\Plugin;

use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use ZenMagick\base\Toolbox;
use ZenMagick\http\sacs\SacsManager;

/**
 * Generic form handler.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class FormHandlerPlugin extends Plugin {

    /**
     * {@inheritDoc}
     */
    public function init() {
        parent::init();

        // mappings
        $pages = $this->get('pages');
        if (0 < strlen($pages)) {
            $pages = explode(',', $pages);
            $settingsService = $this->container->get('settingsService');
            $ext = $settingsService->get('zenmagick.http.templates.ext', '.php');
            $routeResolver = $this->container->get('routeResolver');
            $routeList = array();
            $controller = 'ZenMagick\plugins\formHandler\controller\FormHandlerController';
            foreach ($pages as $page) {
                $routeList[] = array($page, new Route('/'.$page, array('_controller' => $controller), array(), array('view' => 'views/'.$page.$ext, 'view:success' => 'redirect://'.$page)));
            }
            $routeResolver->addRoutes($routeList);

            if (Toolbox::asBoolean($this->get('secure'))) {
                // mark as secure
                foreach ($pages as $page) {
                    $this->container->get('sacsManager')->setMapping($page, \ZMAccount::ANONYMOUS);
                }
            }

            // XXX: authorization, form id?
        }
    }

}
