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

use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use zenmagick\base\Toolbox;
use zenmagick\http\sacs\SacsManager;

/**
 * Generic form handler.
 *
 * @package org.zenmagick.plugins.formHandler
 * @author DerManoMann <mano@zenmagick.org>
 */
class ZMFormHandlerPlugin extends Plugin {

    /**
     * Create new instance.
     */
    public function __construct() {
        parent::__construct('Form Handler', 'Generic form handler with email notification', '${plugin.version}');
    }


    /**
     * Install this plugin.
     */
    public function install() {
        parent::install();

        // options: page name(s);form_name?, email address
        $this->addConfigValue('Pages', 'pages', '', 'Comma separated list of page names that should be handled');
        $this->addConfigValue('Notification email address', 'adminEmail', ZMSettings::get('storeEmail'),
            'Email address for admin notifications (use store email if empty)');
        $this->addConfigValue('Notification template', 'emailTemplate', 'form_handler', 'Name of common notification email template (empty will use the page name as template)');
        $this->addConfigValue('Secure', 'secure', 'false', 'Flag *all* form urls as secure',
            'widget@booleanFormWidget#name=secure&default=false&label=Enforce HTTPS&style=checkbox');
    }

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
            foreach ($pages as $page) {
                $routeList[] = array($page, new Route('/'.$page, array('_controller' => 'ZMFormHandlerController'), array(), array('view' => 'views/'.$page.$ext, 'view:success' => 'redirect://'.$page)));
            }
            $routeResolver->addRoutes($routeList);

            if (Toolbox::asBoolean($this->get('secure'))) {
                // mark as secure
                foreach ($pages as $page) {
                    $this->container->get('sacsManager')->setMapping($page, ZMAccount::ANONYMOUS);
                }
            }

            // XXX: authorization, form id?
        }
    }

}
