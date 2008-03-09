<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2008 ZenMagick
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
 * Hover Box3 Page content handler.
 *
 * <p>This handler will dynamically add all required JavaScript and CSS.</p>
 *
 * @package org.zenmagick.plugins.zm_hoverbox3
 * @author DerManoMann
 * @version $Id$
 */
class H3PageContentHandler extends ZMPluginHandler {

    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
    }

    /**
     * Create new instance.
     */
    function H3PageContentHandler() {
        $this->__construct();
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Filter the response contents.
     *
     * @param string contents The contents.
     * @return string The modified contents.
     */
    function filterResponse($contents) {
    global $zm_theme, $zm_hoverbox3;

        if (strpos($contents, 'hoverbox')) {
            // hover3 used in this page
            $h3head = '';
            $h3head .= '<link rel="stylesheet" type="text/css" href="' . $zm_theme->themeURL('hover3/stylesheet_hoverbox3.css', false) . '" />';
            $h3head .= '<script type="text/javascript" src="' . $zm_theme->themeURL('hover3/ic_effects.js', false) . '"></script>';
            // eval js config
            $h3config_tpl = file_get_contents($zm_hoverbox3->getPluginDir().'/ic_hoverbox_config.tpl');
            ob_start();
            eval('?>'.$h3config_tpl);
            $h3config = ob_get_clean();
            $h3head .= $h3config;
            $h3head .= '<script type="text/javascript" src="' . $zm_theme->themeURL('hover3/ic_hoverbox3.js', false) . '"></script>';
            $contents = preg_replace('/<\/head>/', $h3head.'</head>', $contents, 1);
        }
        return $contents;
    }

}

?>
