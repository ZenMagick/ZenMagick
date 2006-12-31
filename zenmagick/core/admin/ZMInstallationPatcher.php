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

define('_ZM_ZEN_ADMIN_FILE', DIR_WS_BOXES . "extras_dhtml.php");
define('_ZM_ZEN_DIR_FS_BOXES', DIR_FS_CATALOG.DIR_WS_MODULES."sideboxes/");
define('_ZM_ZEN_INDEX_PHP', DIR_FS_CATALOG."index.php");

/**
 * Provides support for all file patching ZenMagick might need.
 *
 * @author mano
 * @package net.radebatz.zenmagick.admin
 * @version $Id$
 */
class ZMInstallationPatcher extends ZMObject {


    /**
     * Default c'tor.
     */
    function ZMInstallationPatcher() {
        parent::__construct();
    }

    /**
     * Default c'tor.
     */
    function __construct() {
        $this->ZMInstallationPatcher();
    }

    /**
     * Default d'tor.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Returns <code>true</code> if any patches left to run.
     *
     * @return bool <code>true</code> if there are any patches left that could be run.
     */
    function isPatchesAvailable() {
        return $this->isAdminRebuildRequired()
            || $this->isCreateZCSideboxesRequired()
            || $this->isCreateZCThemesRequired()
            || $this->isPatchThemeSupportRequired();
    }

    /**
     * Execute all open patches.
     *
     * @param bool force If set to <code>true</code> it will force patching even if
     *  it is disabled as per settings.
     * @return bool <code>true</code> if <strong>all</strong> patching was successful, <code>false</code> if not.
     */
    function patch($force=false) {
        $result = true;
        $result |= $this->rebuildAdmin($force);
        $result |= $this->createZCSideboxes($force);
        $result |= $this->createZCThemes($force);
        $result |= $this->patchThemeSupport($force);
        return $result;
    }

    /**
     * Checks if the admin interface needs to be re-build by patching
     * <code>extras_dhtml.php</code>.
     *
     * @return bool <code>true</code> if patching is required, <code>false</code> if not.
     */
    function isAdminRebuildRequired() {
        $contents = file_get_contents(_ZM_ZEN_ADMIN_FILE);
        return false === strpos($contents, "zenmagick_dhtml.php");
    }

    /**
     * Rebuild the ZenMagick admin interface.
     *
     * @param bool force If set to <code>true</code> it will force patching even if
     *  it is disabled as per settings.
     * @return bool <code>true</code> if the patch was successful, <code>false</code> if not.
     */
    function rebuildAdmin($force=false) {
        if ($this->isAdminRebuildRequired()) {
            if (zm_setting('isAdminAutoRebuild') || $force) {
                // patch
                if (is_writeable(_ZM_ZEN_ADMIN_FILE)) {
                    zm_log("** ZenMagick: patching zen-cart admin to auto-enable ZenMagick admin menu", 1);
                    $handle = fopen(_ZM_ZEN_ADMIN_FILE, "at");
                    fwrite($handle, "\n<?php require(DIR_WS_BOXES . 'zenmagick_dhtml.php'); /* added by ZenMagick auto patcher */ ?>");
                    fclose($handle);
                    return true;
                } else {
                    zm_log("** ZenMagick: no permission to patch zen-cart admin extras_dhtml.php", 1);
                    return false;
                }
            } else {
                // disabled
                zm_log("** ZenMagick: rebuild admin disabled - skipping");
                return false;
            }
        }

        return true;
    }


    /**
     * Checks if there are ZenMagick sideboxes that would require zen-cart dummy files.
     *
     * <p><strong>NOTE:</strong> This is for the current theme only. If you are switching between
     * themes, new dummies might be required...</p>
     *
     * @return bool <code>true</code> if patching is required, <code>false</code> if not.
     */
    function isCreateZCSideboxesRequired() {
        return 0 != count($this->_getMissingZCSideboxes());
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

    /**
     * Create all needed zen-cart sidebox dummy files.
     *
     * @param bool force If set to <code>true</code> it will force patching even if
     *  it is disabled as per settings.
     * @return bool <code>true</code> if the patch was successful, <code>false</code> if not.
     */
    function createZCSideboxes($force=false) {
        $missingBoxes = $this->_getMissingZCSideboxes();

        if (0 < count($missingBoxes)) {
            if (zm_setting('isAutoCreateZCSideboxes') || $force) {
                foreach ($missingBoxes as $box) {
                    if (!file_exists(_ZM_ZEN_DIR_FS_BOXES.$box) && is_writeable(_ZM_ZEN_DIR_FS_BOXES)) {
                        $handle = fopen(_ZM_ZEN_DIR_FS_BOXES.$box, 'at');
                        fwrite($handle, '<?php /** dummy file created by ZenMagick **/ ?>');
                        fclose($handle);
                        return true;
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
     * Checks if there are ZenMagick themes that need zen-cart template dummy files.
     *
     * @return bool <code>true</code> if patching is required, <code>false</code> if not.
     */
    function isCreateZCThemesRequired() {
        //TODO: theme stuff
        $theme = new ZMTheme();
        $themeInfos = $theme->getThemeInfoList();
        foreach ($themeInfos as $themeInfo) {
            if ('default' == $themeInfo->getPath())
                continue;
            if (!file_exists(DIR_FS_CATALOG_TEMPLATES.$themeInfo->getPath())) {
                return true;
            }
        }

        return false;
    }

    /**
     * Create all needed template dummy files.
     *
     * @param bool force If set to <code>true</code> it will force patching even if
     *  it is disabled as per settings.
     * @return bool <code>true</code> if the patch was successful, <code>false</code> if not.
     */
    function createZCThemes($force=false) {
        if (!zm_setting('isAutoCreateZCThemeDummies') && !$force && $this->isPatchThemeSupportRequired()) {
            // disabled
            zm_log("** ZenMagick: create theme dummies disabled - skipping");
            return false;
        }

        //TODO: theme stuff
        $theme = new ZMTheme();
        $themeInfos = $theme->getThemeInfoList();
        foreach ($themeInfos as $themeInfo) {
            if ('default' == $themeInfo->getPath())
                continue;
            if (!file_exists(DIR_FS_CATALOG_TEMPLATES.$themeInfo->getPath())) {
                if (is_writeable(DIR_FS_CATALOG_TEMPLATES)) {
                    mkdir(DIR_FS_CATALOG_TEMPLATES.$themeInfo->getPath());
                    $handle = fopen(DIR_FS_CATALOG_TEMPLATES.$themeInfo->getPath()."/template_info.php", 'at');
                    fwrite($handle, '<?php /** dummy file created by ZenMagick **/'."\n");
                    fwrite($handle, '  $template_version = ' . "'" . addslashes($themeInfo->getVersion()) . "';\n");
                    fwrite($handle, '  $template_name = ' . "'" . addslashes($themeInfo->getName()) . "';\n");
                    fwrite($handle, '  $template_author = ' . "'" . addslashes($themeInfo->getAuthor()) . "';\n");
                    fwrite($handle, '  $template_description = ' . "'" . addslashes($themeInfo->getDescription()) . "';\n");
                    fwrite($handle, '?>');
                    fclose($handle);
                    return true;
                } else {
                    zm_log("** ZenMagick: no permission to create theme dummy ".$themeInfo->getPath(), 1);
                    return false;
                }
            }
        }

        return true;
    }


    /**
     * Checks if the main <code>index.php</code> needs to be patched for ZenMagick theme support.
     *
     * @param array lines File contents as line array.
     * @return bool <code>true</code> if patching is required, <code>false</code> if not.
     */
    function isPatchThemeSupportRequired($lines=null) {
        if (null == $lines) {
            $lines = $this->_loadIndexPHP();
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
     * Load the file contents of <code>index.php</code>.
     *
     * @return array File contents as lines or <code>null</code>.
     */
    function _loadIndexPHP() {
        $lines = array();
        if (file_exists(_ZM_ZEN_INDEX_PHP)) {
            $handle = @fopen(_ZM_ZEN_INDEX_PHP, 'rt');
            if ($handle) {
                while (!feof($handle)) {
                    $line = fgets($handle, 4096);
                    array_push($lines, $line);
                }
                fclose($handle);
            }
        }

        return $lines;
    }

    /**
     * Patch <code>index.php</code>.
     *
     * @param bool force If set to <code>true</code> it will force patching even if
     *  it is disabled as per settings.
     * @return bool <code>true</code> if the patch was successful, <code>false</code> if not.
     */
    function patchThemeSupport($force=false) {
        $lines = $this->_loadIndexPHP();
        if (!$this->isPatchThemeSupportRequired($lines)) {
            return true;
        }

        if (zm_setting('isAdminPatchThemeSupport') || $force) {
            if (is_writeable(_ZM_ZEN_INDEX_PHP)) {
                $patchedLines = array();
                // need to insert before the zen-cart html_header...
                foreach ($lines as $line) {
                    if (false !== strpos($line, "require") && false !== strpos($line, "html_header.php")) {
                        array_push($patchedLines, "  require('zenmagick/store.php'); /* added by ZenMagick auto patcher */\n");
                    }
                    array_push($patchedLines, $line);
                }

                // rewrite index.php
                $handle = fopen(_ZM_ZEN_INDEX_PHP, 'wt');
                foreach ($patchedLines as $line) {
                    fwrite($handle, $line);
                }
                fclose($handle);
                return true;
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
    
}

?>
