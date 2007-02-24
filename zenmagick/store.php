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

    // main request processor
    if (zm_setting('isEnableZenMagick')) {

        // check page cache
        if (zm_setting('isPageCacheEnabled')) {
            $pageCache = $zm_runtime->getPageCache();
            if ($pageCache->isCacheable() && $contents = $pageCache->get()) {
                echo $contents;
                if (zm_setting('isDisplayTimerStats')) {
                    $_zm_db = $zm_runtime->getDB();
                    echo '<!-- stats: ' . round($_zm_db->queryTime(), 4) . ' sec. for ' . $_zm_db->queryCount() . ' queries; ';
                    echo 'page: ' . zm_get_elapsed_time() . ' sec. -->';
                }
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

            // TODO: theme stuff !!!!!!!!!
            $zm_themeInfo = $zm_theme->getThemeInfo();
            // need to do this in global namespace
            $_zm_tstatic = $_zm_themeLoader->getStatic();
            foreach ($_zm_tstatic as $static) {
                require_once($static);
            }

            // TODO: theme switch; allow once only
            // ideally, this should all be called inside ZMRuntime::setThemeId(..)
            // actually, there should be someone else responsible for updaing all this ;)
            // ... like a request dispatcher...
            if ($zm_runtime->getThemeId() != $zm_themeInfo->getThemeId()) {
                $zm_theme = $zm_runtime->getTheme();

                // add new root loader
                $_zm_defaultThemeLoader =& new ZMLoader("defaultThemeLoader");
                $_zm_defaultThemeLoader->addPath($zm_theme->getExtraDir());
                $rootLoader = $zm_loader->getRootLoader();
                $rootLoader->setParent($_zm_defaultThemeLoader);

                $zm_themeInfo = $zm_theme->getThemeInfo();
                // need to do this in global namespace
                $_zm_tstatic = $_zm_defaultThemeLoader->getStatic();
                foreach ($_zm_tstatic as $static) {
                    require_once($static);
                }

                // get controller
                $zm_controller = $zm_loader->create(zm_mk_classname($zm_request->getPageName().'Controller'));
                $zm_controller = null == $zm_controller ? $zm_loader->create("DefaultController") : $zm_controller;
                $zm_request->setController($zm_controller);
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
