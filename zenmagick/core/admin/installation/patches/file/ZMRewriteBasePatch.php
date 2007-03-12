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

define('_ZM_HTACCESS', DIR_FS_CATALOG.".htaccess");


/**
 * Patch to update the <code>.htaccess</code> <code>RewriteBase</code>.
 *
 * @author mano
 * @package net.radebatz.zenmagick.admin.installation.patches.file
 * @version $Id$
 */
class ZMRewriteBasePatch extends ZMInstallationPatch {

    /**
     * Default c'tor.
     */
    function ZMRewriteBasePatch() {
        parent::__construct('rewriteBase');
    }

    /**
     * Default c'tor.
     */
    function __construct() {
        $this->ZMRewriteBasePatch();
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
        $lines = $this->_loadFileLines(_ZM_HTACCESS);
        foreach ($lines as $line) {
            $words = explode(' ', $line);
            if (2 == count($words) && 'RewriteBase' == trim($words[0])) {
                return DIR_WS_CATALOG != $words[1];
            }
        }

        return false;
    }

    /**
     * Checks if this patch is ready to be applied.
     *
     * @return bool <code>true</code> if this patch is ready and all preconditions are met.
     */
    function isReady() {
        return is_writeable(_ZM_HTACCESS);
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
        return $this->isReady() ? "" : "Need permission to write " . _ZM_HTACCESS;
    }

    /**
     * Execute this patch.
     *
     * @param bool force If set to <code>true</code> it will force patching even if
     *  disabled as per settings.
     * @return bool <code>true</code> if patching was successful, <code>false</code> if not.
     */
    function patch($force=false) {
        if (!$this->isReady()) {
            return false;
        }

        if ((zm_setting('isEnablePatching') && zm_setting('isPatchRewriteBase')) || $force) {
            $lines = $this->_loadFileLines(_ZM_HTACCESS);
            $lines = $this->_fixLines($lines);
            $handle = fopen(_ZM_HTACCESS, 'wb');
            foreach ($lines as $line) {
                fwrite($handle, $line."\n");
            }
            fclose($handle);
            return true;
        }

        return true;
    }

   /**
     * Check if this patch supports undo.
     *
     * @return bool <code>true</code> if undo is supported, <code>false</code> if not.
     */
    function canUndo() {
        return false;
    }
    

    /**
     * Fix lines.
     */
    function _fixLines($lines) {
        foreach ($lines as $ii => $line) {
            $words = explode(' ', $line);
            if (2 == count($words) && 'RewriteBase' == trim($words[0])) {
                if (DIR_WS_CATALOG != $words[1]) {
                    // fix (might not get written, though
                    $lines[$ii] = 'RewriteBase ' . DIR_WS_CATALOG;
                }
                break;
            }
        }

        return $lines;
    }

    /**
     * Load the file contents of the given language file.
     *
     * @param string name The filename.
     * @return array File contents as lines or <code>null</code>.
     */
    function _loadFileLines($file) {
        $lines = array();
        if (file_exists($file)) {
            $handle = @fopen($file, 'rb');
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
