<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006,2007 ZenMagick
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
 * Simple plugin view.
 *
 * <p>The content is either a full page or a layout using the page specified in the request.</p>
 *
 * @author mano
 * @package net.radebatz.zenmagick.rp.uip.views
 * @version $Id$
 */
class ZMPluginView extends ZMPageView {
    var $plugin_;


    /**
     * Create new theme view view.
     *
     * @param string page The page (view) name.
     * @param string plugin The plugin.
     */
    function ZMPluginView($page, &$plugin) {
        parent::__construct($page);
        $this->plugin_ =& $plugin;
    }

    /**
     * Create new theme view view.
     *
     * @param string page The page (view) name.
     * @param string plugin The plugin.
     */
    function __construct($page, &$plugin) {
        $this->ZMPluginView($page, $plugin);
    }

    /**
     * Default d'tor.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Returns the full view filename to be included by a template.
     *
     * @param string subdir Optional subdirectory name within the views directory.
     * @param bool $prefixToDir If <code>true</code> the subdir is assumed to be the view filename prefix; eg: 'popup_'. If this is the case,
     *  it gets converted into an additional ssubdir instead. Example: <code>popup_cvv_help.php</code> = <code>popup/cvv_help.php</code>.
     * @return string The full view filename.
     */
    function getViewFilename($subdir=null, $prefixToDir=true) {
        $filename = parent::getViewFilename($subdir, $prefixToDir);
        if (file_exists($filename)) {
            return $filename;
        }

        $plugin = $this->plugin_;
        return $plugin->getPluginDir() . $this->getName() . ".php";
    }

}

?>
