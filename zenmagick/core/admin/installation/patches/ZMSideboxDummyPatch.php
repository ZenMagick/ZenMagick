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

define('_ZM_ZEN_DIR_FS_BOXES', DIR_FS_CATALOG.DIR_WS_MODULES."sideboxes/");


/**
 * Patch to create dummy sidebox files for zen-cart.
 *
 * @author mano
 * @package net.radebatz.zenmagick.admin.installation.patches
 * @version $Id$
 */
class ZMSideboxDummyPatch extends ZMInstallationPatch {

    /**
     * Default c'tor.
     */
    function ZMSideboxDummyPatch() {
        parent::__construct('sideboxDummies');
    }

    /**
     * Default c'tor.
     */
    function __construct() {
        $this->ZMSideboxDummyPatch();
    }

    /**
     * Default d'tor.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Checks if this patch can still be applied.
     *
     * @return bool <code>true</code> if this patch can still be applied.
     */
    function isOpen() {
        return 0 != count($this->_getMissingZCSideboxes());
    }

    /**
     * Checks if this patch is ready to be applied.
     *
     * @return bool <code>true</code> if this patch is ready and all preconditions are met.
     */
    function isReady() {
        return is_writeable(_ZM_ZEN_DIR_FS_BOXES);
    }

    /**
     * Get the precondition message.
     *
     * <p>This will return an empty string when <code>isReady()</code> returns <code>true</code>.</p>
     *
     * @return string The preconditions message or an empty string.
     */
    function getPreconditionsMessage() {
        return $this->isReady() ? "" : "Need permission to write " . _ZM_ZEN_DIR_FS_BOXES;
    }

    /**
     * Execute this patch.
     *
     * @param bool force If set to <code>true</code> it will force patching even if
     *  disabled as per settings.
     * @return bool <code>true</code> if patching was successful, <code>false</code> if not.
     */
    function patch($force=false) {
        $missingBoxes = $this->_getMissingZCSideboxes();

        if (0 < count($missingBoxes)) {
            if (zm_setting('isAutoCreateZCSideboxes') || $force) {
                foreach ($missingBoxes as $box) {
                    if ($this->isReady()) {
                        if (!file_exists(_ZM_ZEN_DIR_FS_BOXES.$box)) {
                            $handle = fopen(_ZM_ZEN_DIR_FS_BOXES.$box, 'at');
                            fwrite($handle, '<?php /** dummy file created by ZenMagick installation patcher **/ ?>');
                            fclose($handle);
                            return true;
                        }
                    } else {
                        zm_log("** ZenMagick: no permission to create dummy sidebox " . $box, 1);
                        return false;
                    }
                }
            } else {
                // disabled
                zm_log("** ZenMagick: create sidebox dummies disabled - skipping");
                return false;
            }
        }

        return true;
    }


    /**
     * Builds a list of all ZenMagick theme sideboxes that do not have zen-cart sidebox dummies.
     *
     * return array List of sideboxes that need zen-cart dummies.
     */
    function _getMissingZCSideboxes() {
    global $zm_runtime;

        $missingBoxes = array();
        $boxPath = $zm_runtime->getThemeBoxPath();
        if (file_exists($boxPath) && is_readable($boxPath)) {
            $handle = opendir($zm_runtime->getThemeBoxPath());
            $zmBoxes = array();
            while (false !== ($file = readdir($handle))) {
                $zmBoxes[$file] = $file;
            }
            closedir($handle);

            $zcBoxes = array();
            $handle = opendir(_ZM_ZEN_DIR_FS_BOXES);
            while (false !== ($file = readdir($handle))) {
                $zcBoxes[$file] = $file;
            }
            closedir($handle);

            foreach ($zmBoxes as $box) {
                if (!array_key_exists($box, $zcBoxes) && '.' != $box && '..' != $box && zm_ends_with($box, '.php')) {
                    $missingBoxes[$box] = $box;
                } 
            }
        }
        
        return $missingBoxes;
    }

}

?>
