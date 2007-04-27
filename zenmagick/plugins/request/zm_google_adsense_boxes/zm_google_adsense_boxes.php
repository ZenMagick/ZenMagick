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
 *
 * $Id$
 */
?>
<?php

// check for local defaults
if (file_exists(dirname(__FILE__)."/config.php")) { include("config.php"); }

// required code
require("zm_google_adsense_code.php");


// some default defaults ;)
define (_ZM_GOOGLE_ADSENSE_BOXES_COUNT, 4);
define (_ZM_GOOGLE_ADSENSE_BOX_PREFIX, 'adsense_box_');
define (_ZM_GOOGLE_ADSENSE_BOX_TEMPLATE, 'box-template.php');

/**
 * Box plugin providing functionallity for one or more Google AdSense sidebar box(es).
 *
 * Example of a box plugin managing multiple sideboxes.
 *
 * @author mano
 * @version $Id$
 */
class zm_google_adsense_boxes extends ZMBoxPlugin {

    /**
     * Default c'tor.
     */
    function zm_google_adsense_boxes() {
        parent::__construct('Google AdSense Boxes', 'Plugin for up to '._ZM_GOOGLE_ADSENSE_BOXES_COUNT.' Google AdSense sideboxes.');
        $this->setKeys($this->getBoxNames());
    }

    /**
     * Default c'tor.
     */
    function __construct() {
        $this->zm_google_adsense_boxes();
    }

    /**
     * Default d'tor.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Install this plugin.
     */
    function install() {
        parent::install();

        for ($ii=1; $ii <= _ZM_GOOGLE_ADSENSE_BOXES_COUNT; ++$ii) {
            $this->addConfigValue('Google AdSense JavaScript #'.$ii, _ZM_GOOGLE_ADSENSE_BOX_PREFIX.$ii, '',
              'The JavaScript provided by Google to display your ads for box #'.$ii, 'zen_cfg_textarea(');
        }
    }

    /**
     * Get the ids/names of the boxes supported by this plugin.
     *
     * @return array List of box names.
     */
    function getBoxNames() {
        $keys = array();
        for ($ii=1; $ii <= _ZM_GOOGLE_ADSENSE_BOXES_COUNT; ++$ii) {
            array_push($keys, _ZM_GOOGLE_ADSENSE_BOX_PREFIX.$ii);
        }
        return $keys;
    }

    /**
     * Get the contents for the given box id.
     *
     * @return string Contents for the box implementation.
     */
    function getBoxContents($id) {
        $contents = file_get_contents(dirname(__FILE__)."/"._ZM_GOOGLE_ADSENSE_BOX_TEMPLATE);

        // make them unique
        $contents = str_replace('BOX_ID', $id, $contents);
        return $contents;
    }

}

?>
