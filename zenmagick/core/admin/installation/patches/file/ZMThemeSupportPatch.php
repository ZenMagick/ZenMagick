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

define('_ZM_ZEN_INDEX_PHP', DIR_FS_CATALOG."index.php");

/**
 * Patch to enable ZenMagick themes.
 *
 * @author mano
 * @package net.radebatz.zenmagick.admin.installation.patches.file
 * @version $Id$
 */
class ZMThemeSupportPatch extends ZMFilePatch {

    /**
     * Default c'tor.
     */
    function ZMThemeSupportPatch() {
        parent::__construct('themeSupport');
    }

    /**
     * Default c'tor.
     */
    function __construct() {
        $this->ZMThemeSupportPatch();
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
     * @param array lines The file contents of <code>index.php</code>.
     * @return bool <code>true</code> if this patch can still be applied.
     */
    function isOpen($lines=null) {
        if (null == $lines) {
            $lines = $this->getFileLines(_ZM_ZEN_INDEX_PHP);
        }

        // look for ZenMagick code...
        foreach ($lines as $line) {
            if (false !== strpos($line, "zenmagick/store.php")) {
                return false;
            }
        }

        return true;
    }

    /**
     * Checks if this patch is ready to be applied.
     *
     * @return bool <code>true</code> if this patch is ready and all preconditions are met.
     */
    function isReady() {
        return is_writeable(_ZM_ZEN_INDEX_PHP);
    }

    /**
     * Get the precondition message.
     *
     * <p>This will return an empty string when <code>isReady()</code> returns <code>true</code>.</p>
     *
     * @return string The preconditions message or an empty string.
     */
    function getPreconditionsMessage() {
        return $this->isReady() ? "" : "Need permission to write " . _ZM_ZEN_INDEX_PHP;
    }

    /**
     * Execute this patch.
     *
     * @param bool force If set to <code>true</code> it will force patching even if
     *  disabled as per settings.
     * @return bool <code>true</code> if patching was successful, <code>false</code> if not.
     */
    function patch($force=false) {
        $lines = $this->getFileLines(_ZM_ZEN_INDEX_PHP);
        if (!$this->isOpen($lines)) {
            return true;
        }

        if ((zm_setting('isEnablePatching') && zm_setting('isAdminPatchThemeSupport')) || $force) {
            if (is_writeable(_ZM_ZEN_INDEX_PHP)) {
                $patchedLines = array();
                // need to insert before the zen-cart html_header...
                foreach ($lines as $line) {
                    if (false !== strpos($line, "require") && false !== strpos($line, "html_header.php")) {
                        array_push($patchedLines, "  require('zenmagick/store.php'); /* added by ZenMagick installation patcher */");
                    }
                    array_push($patchedLines, $line);
                }

                return $this->putFileLines(_ZM_ZEN_INDEX_PHP, $patchedLines);
            } else {
                zm_log("** ZenMagick: no permission to patch theme support into index.php", 1);
                return false;
            }
        } else {
            // disabled
            zm_log("** ZenMagick: patch theme support disabled - skipping");
            return false;
        }

        return true;
    }

    /**
     * Revert the patch.
     *
     * @return bool <code>true</code> if patching was successful, <code>false</code> if not.
     */
    function undo() {
        $lines = $this->getFileLines(_ZM_ZEN_INDEX_PHP);
        if ($this->isOpen($lines)) {
            return true;
        }

        if (is_writeable(_ZM_ZEN_INDEX_PHP)) {
            // rewrite index.php
            $handle = fopen(_ZM_ZEN_INDEX_PHP, 'wb');
            foreach ($lines as $line) {
                if (false !== strpos($line, "require") && false !== strpos($line, "zenmagick/store.php")) {
                    continue;
                }
                fwrite($handle, $line."\n");
            }
            fclose($handle);
        } else {
            zm_log("** ZenMagick: no permission to patch index.php for uninstall", 1);
            return false;
        }

        return true;
    }
    
}

?>
