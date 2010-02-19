<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2009 zenmagick.org
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
define('_ZM_ZEN_DIR_FS_ADMIN_LANGUAGES', DIR_FS_ADMIN.DIR_WS_LANGUAGES);
define('_ZM_INIT_TEMPLATES_FILE', DIR_FS_ADMIN . DIR_WS_INCLUDES . 'init_includes/overrides/init_templates.php');


/**
 * Patch to enable ZenMagick's i18n support.
 *
 * @author DerManoMann
 * @package org.zenmagick.store.admin.installation.patches.file
 * @version $Id$
 */
class ZMI18nPatch extends ZMFilePatch {

    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct('i18nSupport');
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Checks if this patch can still be applied.
     *
     * @return boolean <code>true</code> if this patch can still be applied.
     */
    function isOpen() {
        $isUnpatched = 0 != count($this->_getUnpatchedFiles());

        $isI18n = false;
        $lines = $this->getFileLines(_ZM_INIT_TEMPLATES_FILE);
        foreach ($lines as $ii => $line) {
            if (false !== strpos($line, '$zmLanguagesBefore = true;')) {
                $isI18n = true;
                break;
            }
        }

        return $isUnpatched || $isI18n;
    }

    /**
     * Checks if this patch is ready to be applied.
     *
     * @return boolean <code>true</code> if this patch is ready and all preconditions are met.
     */
    function isReady() {
        $files = $this->_getUnpatchedFiles();
        foreach ($files as $file => $lines) {
            if (!is_writeable($file)) {
                return false;
            }
        }

        return is_writeable(_ZM_INIT_TEMPLATES_FILE);
    }

    /**
     * Get the precondition message.
     *
     * <p>This will return an empty string when <code>isReady()</code> returns <code>true</code>.</p>
     *
     * @return string The preconditions message or an empty string.
     */
    function getPreconditionsMessage() {
        return $this->isReady() ? "" : "Need permission to write " . _ZM_ZEN_DIR_FS_LANGUAGES . ", " . 
          _ZM_ZEN_DIR_FS_ADMIN_LANGUAGES . " and containing .php files and " . _ZM_INIT_TEMPLATES_FILE;
    }

    /**
     * Execute this patch.
     *
     * @param boolean force If set to <code>true</code> it will force patching even if
     *  disabled as per settings.
     * @return boolean <code>true</code> if patching was successful, <code>false</code> if not.
     */
    function patch($force=false) {
        if ((ZMSettings::get('isEnablePatching')) || $force) {
            // patch lang files
            $files = $this->_getUnpatchedFiles();
            foreach ($files as $file => $lines) {
                $this->putFileLines($file, $lines);
            }

            // patch admin lang loading sequence
            $lines = $this->getFileLines(_ZM_INIT_TEMPLATES_FILE);
            foreach ($lines as $ii => $line) {
                if (false !== strpos($line, '$zmLanguagesBefore = true;')) {
                    $lines[$ii] = str_replace("true", "false", $line);
                }
            }
            $this->putFileLines(_ZM_INIT_TEMPLATES_FILE, $lines);

            return true;
        }

        return true;
    }
    
    /**
     * Generates a list of all patched zen-cart language files.
     *
     * @return array Hash with filename as key and contents as array of lines as value.
     */
    function _getPatchedFiles() {
        $files = array();
        if (file_exists(_ZM_ZEN_DIR_FS_LANGUAGES) && is_readable(_ZM_ZEN_DIR_FS_LANGUAGES)) {
            $handle = opendir(_ZM_ZEN_DIR_FS_LANGUAGES);
            while (false !== ($file = readdir($handle))) {
                if (ZMLangUtils::endsWith($file, '.php')) {
                    $lines = $this->getFileLines(_ZM_ZEN_DIR_FS_LANGUAGES.$file);
                    $files[_ZM_ZEN_DIR_FS_LANGUAGES.$file] = $lines;
                }
            }
            closedir($handle);
        }

        if (file_exists(_ZM_ZEN_DIR_FS_ADMIN_LANGUAGES) && is_readable(_ZM_ZEN_DIR_FS_ADMIN_LANGUAGES)) {
            $handle = opendir(_ZM_ZEN_DIR_FS_ADMIN_LANGUAGES);
            while (false !== ($file = readdir($handle))) {
                if (ZMLangUtils::endsWith($file, '.php')) {
                    $lines = $this->getFileLines(_ZM_ZEN_DIR_FS_ADMIN_LANGUAGES.$file);
                    $files[_ZM_ZEN_DIR_FS_ADMIN_LANGUAGES.$file] = $lines;
                }
            }
            closedir($handle);
        }

        return $files;
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
                if (ZMLangUtils::endsWith($file, '.php')) {
                    $lines = $this->getFileLines(_ZM_ZEN_DIR_FS_LANGUAGES.$file);
                    foreach ($lines as $ii => $line) {
                        if (false !== strpos($line, "function ") && false !== strpos($line, "zen_date_raw(") && false === strpos($line, "_DISABLED ")) {
                            // change already here
                            $lines[$ii] = str_replace('zen_date_raw', 'zen_date_raw_DISABLED', $line);
                            $lines[$ii] = trim($lines[$ii]) . " /* modified by ZenMagick installation patcher */";
                            // store in array
                            $files[_ZM_ZEN_DIR_FS_LANGUAGES.$file] = $lines;
                            break;
                        }
                    }
                }
            }
            closedir($handle);
        }

        if (file_exists(_ZM_ZEN_DIR_FS_ADMIN_LANGUAGES) && is_readable(_ZM_ZEN_DIR_FS_ADMIN_LANGUAGES)) {
            $handle = opendir(_ZM_ZEN_DIR_FS_ADMIN_LANGUAGES);
            while (false !== ($file = readdir($handle))) {
                if (ZMLangUtils::endsWith($file, '.php')) {
                    $lines = $this->getFileLines(_ZM_ZEN_DIR_FS_ADMIN_LANGUAGES.$file);
                    foreach ($lines as $ii => $line) {
                        if (false !== strpos($line, "function ") && false !== strpos($line, "zen_date_raw(") && false === strpos($line, "_DISABLED ")) {
                            // change already here
                            $lines[$ii] = str_replace('zen_date_raw', 'zen_date_raw_DISABLED', $line);
                            $lines[$ii] = trim($lines[$ii]) . " /* modified by ZenMagick installation patcher */";
                            // store in array
                            $files[_ZM_ZEN_DIR_FS_ADMIN_LANGUAGES.$file] = $lines;
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
     * Revert the patch.
     *
     * @return boolean <code>true</code> if patching was successful, <code>false</code> if not.
     */
    function undo() {
        if ($this->isOpen()) {
            return true;
        }

        $status = true;
        foreach ($this->_getPatchedFiles() as $file => $lines) {
            // fix patched line
            foreach ($lines as $ii => $line) {
                if (false !== strpos($line, "function ") && false !== strpos($line, "zen_date_raw_DISABLED(")) {
                    $lines[$ii] = str_replace('_DISABLED', '', $lines[$ii]);
                    $lines[$ii] = str_replace(' /* modified by ZenMagick installation patcher */', '', $lines[$ii]);
                }
            }
            $this->putFileLines($file, $lines);
        }

        // patch admin lang loading sequence
        $lines = $this->getFileLines(_ZM_INIT_TEMPLATES_FILE);
        foreach ($lines as $ii => $line) {
            if (false !== strpos($line, '$zmLanguagesBefore = false;')) {
                $lines[$ii] = str_replace("false", "true", $line);
            }
        }
        $this->putFileLines(_ZM_INIT_TEMPLATES_FILE, $lines);

        return true;
    }
    
}
