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

define('_ZM_ZEN_DIR_FS_LANGUAGES', DIR_FS_CATALOG_LANGUAGES);


/**
 * Patch to enable ZenMagick's i18n support.
 *
 * @author mano
 * @package net.radebatz.zenmagick.admin.installation.patches.file
 * @version $Id$
 */
class ZMI18nPatch extends ZMInstallationPatch {

    /**
     * Default c'tor.
     */
    function ZMI18nPatch() {
        parent::__construct('i18nSupport');
    }

    /**
     * Default c'tor.
     */
    function __construct() {
        $this->ZMI18nPatch();
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
        return 0 != count($this->_getUnpatchedFiles());
    }

    /**
     * Checks if this patch is ready to be applied.
     *
     * @return bool <code>true</code> if this patch is ready and all preconditions are met.
     */
    function isReady() {
        $files = $this->_getUnpatchedFiles();
        foreach ($files as $file => $lines) {
            if (!is_writeable(_ZM_ZEN_DIR_FS_LANGUAGES . $file)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get the patch group id.
     *
     * @return string The patch group id.
     */
    function getGroupId() {
        return 'file';
    }

    /**
     * Get the precondition message.
     *
     * <p>This will return an empty string when <code>isReady()</code> returns <code>true</code>.</p>
     *
     * @return string The preconditions message or an empty string.
     */
    function getPreconditionsMessage() {
        return $this->isReady() ? "" : "Need permission to write " . _ZM_ZEN_DIR_FS_LANGUAGES . " and containing .php files";
    }

    /**
     * Execute this patch.
     *
     * @param bool force If set to <code>true</code> it will force patching even if
     *  disabled as per settings.
     * @return bool <code>true</code> if patching was successful, <code>false</code> if not.
     */
    function patch($force=false) {
        if ((zm_setting('isEnablePatching') && zm_setting('isI18nSupport')) || $force) {
            $files = $this->_getUnpatchedFiles();
            foreach ($files as $file => $lines) {
                $handle = fopen(_ZM_ZEN_DIR_FS_LANGUAGES . $file, 'wb');
                // avoid last linefeed
                $first = true;
                foreach ($lines as $line) {
                    if (!$first) $line = "\n".$line;
                    fwrite($handle, $line);
                    $first = false;
                }
                fclose($handle);
                return true;
            }
        }

        return true;
    }
    

    /**
     * Generates a list of all unpatched zen-cart language files.
     *
     * @return array Hash with filename as key and contents as array of lines as value.
     */
    function _getUnpatchedFiles() {
        $files = array();
        if (file_exists(_ZM_ZEN_DIR_FS_LANGUAGES) && is_readable(_ZM_ZEN_DIR_FS_LANGUAGES)) {
            $handle = opendir(_ZM_ZEN_DIR_FS_LANGUAGES);
            while (false !== ($file = readdir($handle))) {
                if (zm_ends_with($file, '.php')) {
                    $lines = $this->_loadFileLines($file);
                    foreach ($lines as $ii => $line) {
                        if (false !== strpos($line, "function ") && false !== strpos($line, "zen_date_raw(") && false === strpos($line, "_DISABLED ")) {
                            // change already here
                            $lines[$ii] = str_replace('zen_date_raw', 'zen_date_raw_DISABLED', $line);
                            $lines[$ii] = trim($lines[$ii]) . " /* modified by ZenMagick installation patcher */";
                            // store in array
                            $files[$file] = $lines;
                            break;
                        }
                    }
                }
            }
            closedir($handle);
        }

        return $files;
    }
    
    /**
     * Load the file contents of the given language file.
     *
     * @param string name The filename.
     * @return array File contents as lines or <code>null</code>.
     */
    function _loadFileLines($file) {
        $lines = array();
        if (file_exists(_ZM_ZEN_DIR_FS_LANGUAGES.$file)) {
            $handle = @fopen(_ZM_ZEN_DIR_FS_LANGUAGES.$file, 'rb');
            if ($handle) {
                while (!feof($handle)) {
                    $line = rtrim(fgets($handle, 4096));
                    array_push($lines, $line);
                }
                fclose($handle);
            }
        }

        return $lines;
    }
    
}

?>
