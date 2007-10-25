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

define('_ZM_ZEN_DIR_FS_BOXES', DIR_FS_CATALOG . DIR_WS_MODULES . "sideboxes/");


/**
 * (Side-)box plugin.
 *
 * @author mano
 * @package org.zenmagick.plugins.types
 * @version $Id$
 */
class ZMBoxPlugin extends ZMPlugin {

    /**
     * Create new plugin.
     *
     * @param string title The title.
     * @param string description The description.
     * @param int sortOrder The default sortOrder; defaults to <code>0</code>.
     * @param string configPrefix The configuration key prefix; defaults to [PLUGIN]_[PLUGIN-CODE]_.
     */
    function ZMBoxPlugin($title='', $description='', $sortOrder=0, $configPrefix=null) {
        parent::__construct($title, $description, $sortOrder, $configPrefix);
    }

    /**
     * Create new plugin.
     *
     * @param string title The title.
     * @param string description The description.
     * @param int sortOrder The default sortOrder; defaults to <code>0</code>.
     * @param string configPrefix The configuration key prefix; defaults to [PLUGIN]_[PLUGIN-CODE]_.
     */
    function __construct($title='', $description='', $sortOrder=0, $configPrefix=null) {
        $this->ZMBoxPlugin($title, $description, $sortOrder, $configPrefix);
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

        $this->createBoxes();
    }

    /**
     * Remove this plugin.
     */
    function remove() {
        parent::remove();

        $this->removeBoxes();
    }

    /**
     * Create zen-cart dummy sideboxes plus default boxes for the default theme.
     */
    function createBoxes() {
        // zen-cart dummies
        foreach ($this->getBoxNames() as $box) {
            $file = _ZM_ZEN_DIR_FS_BOXES.$box . '.php';
            if (!file_exists($file)) {
                $handle = fopen($file, 'ab');
                fwrite($handle, '<?php /** dummy file created by ZenMagick installation patcher **/ ?>');
                fclose($handle);
            }
        }

        // default theme
        $theme = new ZMTheme(ZM_DEFAULT_THEME);
        $themeBoxesDir = $theme->getBoxesDir();
        foreach ($this->getBoxNames() as $box) {
            $file = $themeBoxesDir.$box . '.php';
            if (!file_exists($file)) {
                $handle = fopen($file, 'ab');
                fwrite($handle, $this->getBoxContents($box));
                fclose($handle);
            }
        }
    }

    /**
     * Remove zen-cart dummy sideboxes plus default boxes for the default theme.
     */
    function removeBoxes() {
        // zen-cart dummies
        foreach ($this->getBoxNames() as $box) {
            $file = _ZM_ZEN_DIR_FS_BOXES.$box . '.php';
            if (file_exists($file)) {
                unlink($file);
            }
        }

        // default theme
        $theme = new ZMTheme(ZM_DEFAULT_THEME);
        $themeBoxesDir = $theme->getBoxesDir();
        foreach ($this->getBoxNames() as $box) {
            $file = $themeBoxesDir.$box . '.php';
            if (file_exists($file)) {
                unlink($file);
            }
        }
    }

    /**
     * Get the ids/names of the boxes supported by this plugin.
     *
     * @return array List of box names.
     */
    function getBoxNames() {
        return array();
    }

    /**
     * Get the contents for the given box id.
     *
     * @return string Contents for the box implementation.
     */
    function getBoxContents($id) {
        return "<?php /* ZenMagick box created by " . $this->getName() . " */ ?>\n\n<h3>" . $id . ",/h3\n";
    }

    /**
     * Get the index from the given box id.
     *
     * <p>This is assuming that the index is the suffix, separated by '_'.</p>
     *
     * @return int The index or <code>0</code>.
     */
    function getBoxIndex($id) {
        $off = strrpos($id, '_');
        if (null !== $off) {
            return (int)substr($id, $off+1);
        }

        return 0;
    }

}

?>
