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
 * Themes.
 *
 * @author mano
 * @package net.radebatz.zenmagick.service
 * @version $Id$
 */
class ZMThemes extends ZMService {


    /**
     * Default c'tor.
     */
    function ZMThemes() {
        parent::__construct();
    }

    /**
     * Default c'tor.
     */
    function __construct() {
        $this->ZMThemes();
    }

    /**
     * Default d'tor.
     */
    function __destruct() {
        parent::__destruct();
    }

    /**
     * Get <code>ZMThemeInfo</code> instance for the current (or given) theme Id.
     *
     * @param string themeId The theme id or <code>null</code> for the current theme id.
     * @return ZMThemeInfo The themes <code>ZMThemeInfo</code> implementation or <code>null</code>.
     */
    function &getThemeInfoForId($themeId=null) {
    global $zm_runtime;

        // theme id
        $themeId = null == $themeId ? $zm_runtime->getThemeId() : $themeId;
        // theme base path
        $basePath = $zm_runtime->getThemesDir();
        $infoName = $themeId. ' ThemeInfo';
        // theme info class name
        $infoClass = zm_mk_classname($infoName);
        // theme info file name
        $infoFile = $basePath.$themeId."/".$infoClass.".php";
        // load
        require_once($infoFile);
        // create instance
        $obj =& new $infoClass($parent);
        $obj->setThemeId($themeId);
        if ($themeId != ZM_DEFAULT_THEME) {
            $obj->setParent($this->getThemeInfoForId(ZM_DEFAULT_THEME));
        }

        return $obj;
    }

    /**
     * Get a list of all available themes.
     *
     * @return array A list of <code>ZMThemeInfo</code> instances.
     */ 
    function getThemeInfoList() {
    global $zm_runtime;

        $infoList = array();
        $basePath = $zm_runtime->getThemesDir();
        $dirs = $this->_getThemeDirList();
        // load info classes and get instance
        foreach ($dirs as $dir) {
            // assuming that directory name corresponds with theme id
            array_push($infoList, $this->getThemeInfoForId($dir));
        }

        return $infoList;
    }

    /**
     * Generate a list of all theme directories.
     *
     * @return array List of all directories under <em>themes</em> that contain a theme.
     */
    function _getThemeDirList() {
    global $zm_runtime;

        $themes = array();
        $handle = @opendir($zm_runtime->getThemesDir());
        while (false !== ($file = readdir($handle))) { 
            if (zm_starts_with($file, '.') || 'CVS' == $file)
                continue;
            array_push($themes, $file);
        }
        @closedir($handle);
        return $themes;
    }

    /**
     * Get the configured zen-cart theme id (aka the template directory name).
     *
     * @param int languageId Optional language id.
     * @return string The configured zen-cart theme id.
     */
    function getZCThemeId($languageId=null) {
    global $zm_runtime;

        if (null === $languageId) {
            $languageId = $zm_runtime->getLanguageId();
        }

        $sql = "select template_dir
                from " . TABLE_TEMPLATE_SELECT . "
                where template_language = 0";
        $results = $this->getDB()->Execute($sql);
        $themeId = $results->fields['template_dir'];

        $sql = "select template_dir
                from " . TABLE_TEMPLATE_SELECT . "
                where template_language = :languageId";
        $sql = $this->getDB()->bindVars($sql, ":languageId", $languageId, 'integer');
        $results = $this->getDB()->Execute($sql);
        if ($results->RecordCount() > 0) {
            $themeId = $results->fields['template_dir'];
        }

        return $themeId;
    }

}

?>
