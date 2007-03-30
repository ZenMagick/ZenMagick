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
 * Patch to create zen-cart theme dummy files for all ZenMagick themes.
 *
 * @author mano
 * @package net.radebatz.zenmagick.admin.installation.patches.file
 * @version $Id$
 */
class ZMThemeDummyPatch extends ZMFilePatch {

    /**
     * Default c'tor.
     */
    function ZMThemeDummyPatch() {
        parent::__construct('themeDummies');
    }

    /**
     * Default c'tor.
     */
    function __construct() {
        $this->ZMThemeDummyPatch();
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
    global $zm_runtime;

        $themes = $zm_runtime->getThemes();
        foreach ($themes->getThemeInfoList() as $themeInfo) {
            if (ZM_DEFAULT_THEME == $themeInfo->getThemeId()) {
                continue;
            }
            if (!file_exists(DIR_FS_CATALOG_TEMPLATES.$themeInfo->getThemeId())) {
                return true;
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
        return is_writeable(DIR_FS_CATALOG_TEMPLATES);
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
        return $this->isReady() ? "" : "Need permission to write " . DIR_FS_CATALOG_TEMPLATES;
    }

    /**
     * Execute this patch.
     *
     * @param bool force If set to <code>true</code> it will force patching even if
     *  disabled as per settings.
     * @return bool <code>true</code> if patching was successful, <code>false</code> if not.
     */
    function patch($force=false) {
    global $zm_runtime;

        $themes = $zm_runtime->getThemes();
        if (!(zm_setting('isEnablePatching') && zm_setting('isAutoCreateZCThemeDummies')) && !$force && $this->isOpen()) {
            // disabled
            zm_log("** ZenMagick: create theme dummies disabled - skipping");
            return false;
        }

        foreach ($themes->getThemeInfoList() as $themeInfo) {
            if (ZM_DEFAULT_THEME == $themeInfo->getThemeId()) {
                continue;
            }
            if (!file_exists(DIR_FS_CATALOG_TEMPLATES.$themeInfo->getThemeId())) {
                if (is_writeable(DIR_FS_CATALOG_TEMPLATES)) {
                    mkdir(DIR_FS_CATALOG_TEMPLATES.$themeInfo->getThemeId());
                    $handle = fopen(DIR_FS_CATALOG_TEMPLATES.$themeInfo->getThemeId()."/template_info.php", 'ab');
                    fwrite($handle, '<?php /** dummy file created by ZenMagick installation patcher **/'."\n");
                    fwrite($handle, '  $template_version = ' . "'" . addslashes($themeInfo->getVersion()) . "';\n");
                    fwrite($handle, '  $template_name = ' . "'" . addslashes($themeInfo->getName()) . "';\n");
                    fwrite($handle, '  $template_author = ' . "'" . addslashes($themeInfo->getAuthor()) . "';\n");
                    fwrite($handle, '  $template_description = ' . "'" . addslashes($themeInfo->getDescription()) . "';\n");
                    fwrite($handle, '?>');
                    fclose($handle);
                } else {
                    zm_log("** ZenMagick: no permission to create theme dummy ".$themeInfo->getThemeId(), 1);
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Revert the patch.
     *
     * @return bool <code>true</code> if patching was successful, <code>false</code> if not.
     */
    function undo() {
        $dummies = $this->_getDummies();
        foreach ($dummies as $file) {
            // avoid recursive delete, just in case
            @unlink($file."/template_info.php");
            zm_rmdir($file, false);
        }

        return true;
    }
    

    /**
     * Find all dummies.
     *
     * @return array A list of dummy templates.
     */
    function _getDummies() {
        $dummies = array();
        if (file_exists(DIR_FS_CATALOG_TEMPLATES)) {
            $handle = opendir(DIR_FS_CATALOG_TEMPLATES);
            while (false !== ($file = readdir($handle))) {
                if (is_dir(DIR_FS_CATALOG_TEMPLATES.$file) && !zm_starts_with($file, '.')) {
                    $contents = file_get_contents(DIR_FS_CATALOG_TEMPLATES.$file."/template_info.php");
                    if (false !== strpos($contents, 'created by ZenMagick')) {
                        array_push($dummies, DIR_FS_CATALOG_TEMPLATES.$file);
                    }
                }
            }
            closedir($handle);
        }

        return $dummies;
    }

}

?>
