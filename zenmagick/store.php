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

    // main theme handler
    $zm_theme = new ZMTheme();
    $zm_controller = $zm_loader->newInstance(zm_mk_classname($zm_request->getPageName().'Controller'));
    
    if (zm_setting('isEnableZenMagick') && ($zm_theme->isValidRequest() || null != $zm_controller)) {
        // load theme's extra resources
        foreach ($zm_theme->getExtraFiles() as $shared) {
            include_once($shared);
        }
        $zm_themeInfo = $zm_theme->getThemeInfo();
        $zm_view = null;

        // use default if we really want to process this request
        if (null == $zm_controller) {
            $zm_controller = $zm_loader->newInstance("DefaultController");
        }

        /*
         * Either we can go directly to the template (no controller)
         * or the controller may change the response view
         * NOTE: this is not foolproof and only for step by step merging of templates.
         */
        if (null != $zm_controller) {
            if ($zm_controller->process()) {
                $zm_view = $zm_controller->getResponseView();
                // *export* globals from controller into view space
                foreach ($zm_controller->getGlobals() as $name => $instance) {
                    $$name = $instance;
                }
            } else {
                $errorpage = $zm_themeInfo->getErrorPage();
                $zm_view = new ZMView($errorpage, $errorpage);
            }
        } else {
            // use default view
            $zm_view = new ZMView($zm_request->getPageName(), $zm_request->getPageName());
        }

        //TODO cleanup
        // process response view
        $zm_content_include = null;
        if ($zm_view->isRedirectView()) {
            zm_redirect($zm_view->getContentName());
            zm_exit();
        } else if ($zm_view->isUsingTiles()) {
            // ugh!
            if (!file_exists($zm_theme->themeFile($zm_themeInfo->getViewDir().$zm_view->getContentName().'.php'))) {
                $errorpage = $zm_themeInfo->getErrorPage();
                $zm_view = new ZMView($errorpage, $errorpage);
            }
            // expected to be in views
            $zm_content_include = $zm_view->getContentName();
            include($zm_theme->getThemePath($zm_view->getTemplateName().'.php'));
        } else {
            // content is full page
            include($zm_theme->getThemePath($zm_view->getContentName()));
        }

        require('includes/application_bottom.php');
        exit;
    } else if ($current_page == 'admin') {
        require('zenmagick/admin/l10n_create.php');
        require('includes/application_bottom.php');
        exit;
    }

    // default to zen-cart

?>
