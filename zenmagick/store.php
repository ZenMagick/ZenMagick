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
 *
 * $Id$
 */
?>
<?php

    // needs to be instantiated after application_top.php
    $zm_messages = new ZMMessages();
    $zm_categories->setPath($cPath_array);

    // TODO: theme stuff
    $zm_theme = new ZMTheme();

    // main request processor
    if (zm_setting('isEnableZenMagick')) {

        // check page cache
        if (zm_setting('isPageCacheEnabled')) {
            $pageCache = $zm_runtime->getPageCache();
            if ($pageCache->isCacheable() && $contents = $pageCache->get()) {
                echo $contents;
                require('includes/application_bottom.php');
                if (zm_setting('isEnableOB')) { ob_end_flush(); }
                exit;
            }
        }

        // get controller
        $zm_controller = $zm_loader->create(zm_mk_classname($zm_request->getPageName().'Controller'));
        $zm_controller = null == $zm_controller ? $zm_loader->create("DefaultController") : $zm_controller;
        $zm_request->setController($zm_controller);
        if ($zm_controller->validateRequest()) {
            // TODO: theme stuff
            $zm_themeInfo = $zm_theme->getThemeInfo();
            // need to do this in global namespace
            $_zm_tstatic = $themeLoader->getStatic();
            // load local.php first
            if (array_key_exists('local', $_zm_tstatic)) {
                require_once($_zm_tstatic['local']);
            }
            foreach ($_zm_tstatic as $static) {
                require_once($static);
            }

            // execute controller
            $_zm_view = $zm_controller->process();

            // generate response
            if (null != $_zm_view) {
                $_zm_view->generate();
            }

            require('includes/application_bottom.php');
            if (zm_setting('isEnableOB')) { ob_end_flush(); }
            exit;
        }
    }

    // default to zen-cart

?>
