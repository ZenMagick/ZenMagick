<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006 ZenMagick
 *
 * Portions Copyright (c) 2003 The zen-cart developers
 * Portions Copyright (c) 2003 osCommerce
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
 * Dispatch request to the controller in question.
 *
 * <p>During theme switching, the last good controller will be used.</p>
 *
 * @author mano
 * @package net.radebatz.zenmagick
 * @version $Id$
 */
class ZMRequestDispatcher extends ZMObject {
    var $currentController_;

    /**
     * Default c'tor.
     */
    function ZMRequestDispatcher() {
        parent::__construct();

        $this->currentController_ = null;
    }

    /**
     * Default c'tor.
     */
    function __construct() {
        $this->ZMRequestDispatcher();
    }

    /**
     * Default d'tor.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Dispatch the current request.
     *
     * <p>This is a <strong>central</strong> methid in the ZenMagick request process. It will:</p>
     * <ol>
     *  <li>Configure the theme loader to add theme specific code (controller) to the classpath</li>
     *  <li>Determine the controller responsible for handling this request
     *  <li>Check that the controller is indeed happy to handle the request (see <code>ZMController::isValid()</code>)</li>
     *  <li>Load the theme specific <code>extra</code> code</li>
     *  <li>call the controllers <code>process()</code> method</li>
     *  <li>Check for a returned <code>ZMView</code> and if exists, call the views <code>generate()</code> method</li>
     * </ol>
     *
     * @return bool <code>true</code> if the request was dispatched, <code>false</code> if not.
     * @todo l10n/i18n are not updated during theme switches
     */
    function dispatch() {
    global $zm_runtime, $zm_request, $zm_theme, $zm_themeInfo;

        // get root loader
        $_rootLoader =& $this->loader_;
        while (null != $_rootLoader->parent_) {
            $_rootLoader =& $_rootLoader->parent_;
        }

        // set up theme
        $_theme = $zm_runtime->getTheme();
        $_themeInfo = $_theme->getThemeInfo();
        // configure theme loader
        $_themeLoader =& new ZMLoader("themeLoader");
        $_themeLoader->addPath($_theme->getExtraDir());

        // add loader to root loader
        $_rootLoader->setParent($_themeLoader);

        // these can be replaced by themes; will be reinitializes durin theme switching
    global $zm_crumbtrail, $zm_meta;
        $zm_crumbtrail =& $this->create('Crumbtrail');
        $zm_meta =& $this->create('MetaTags');


        $controller =& $this->create(zm_mk_classname($zm_request->getPageName().'Controller'));
        if (null == $controller) {
            if (null != $this->currentController_) {
                // keep last themes controller
                $controller = $this->currentController_;
            } else {
                $controller = $this->create("DefaultController");
            }
        }

        if ($controller->validateRequest()) {
            $zm_request->setController($controller);
            $this->currentController_ = $controller;

            eval(zm_globals());
            /*
            // prepare ZenMagick globals
            foreach ($GLOBALS as $name => $instance) {
                if (zm_starts_with($name, "zm_")) {
                    $$name = $instance;
                }
            }
             */

            // add theme, etc
            $zm_theme = $_theme;
            $zm_themeInfo = $_themeInfo;

            // use theme loader to load static stuff
            foreach ($_themeLoader->getStatic() as $static) {
                require_once($static);
            }

            // check for theme switching
            if ($zm_runtime->getThemeId() != $_themeInfo->getThemeId()) {
                return $this->dispatch();
            }

            // execute controller
            $view = $controller->process();

            // generate response
            if (null != $view) {
                $view->generate();
            }

            return true;
        }

        return false;
    }

}

?>
