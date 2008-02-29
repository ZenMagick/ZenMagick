<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2008 ZenMagick
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
 * @package org.zenmagick.service
 * @version $Id$
 */
class ZMThemes extends ZMObject {

    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
    }

    /**
     * Destruct instance.
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
    function getThemeInfoForId($themeId=null) {
    global $zm_runtime;

        // theme id
        $themeId = null == $themeId ? $zm_runtime->getThemeId() : $themeId;
        // theme base path
        $basePath = $zm_runtime->getThemesDir();
        $infoName = $themeId. ' ThemeInfo';
        // theme info class name
        $infoClass = ZMLoader::makeClassname($infoName);
        // theme info file name
        $infoFile = $basePath.$themeId."/".$infoClass.".php";

        // load
        if (!class_exists($infoClass)) {
            if (!file_exists($infoFile)) {
                $this->log('skipping "' . $themeId . '" - no theme info class found', ZM_LOG_WARN);
                return null;
            }
            require_once($infoFile);
        }
        // create instance
        $obj = new $infoClass();
        $obj->setThemeId($themeId);
        if ($themeId != ZM_DEFAULT_THEME && zm_setting('isEnableThemeDefaults')) {
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
            $themeInfo = $this->getThemeInfoForId($dir);
            if (null != $themeInfo) {
                $infoList[] = $themeInfo;
            }
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
            if (zm_starts_with($file, '.') || 'CVS' == $file) {
                continue;
            }
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
    global $zm_request;

        if (null === $languageId) {
            $session = $zm_request->getSession();
            $languageId = $session->getLanguageId();
        }

        $db = ZMRuntime::getDB();
        $sql = "select template_dir
                from " . TABLE_TEMPLATE_SELECT . "
                where template_language = :languageId";
        $sql = $db->bindVars($sql, ":languageId", $languageId, 'integer');
        $results = $db->Execute($sql);
        if (0 < $results->RecordCount()) {
            $themeId = $results->fields['template_dir'];
        } else {
            $sql = "select template_dir
                    from " . TABLE_TEMPLATE_SELECT . "
                    where template_language = 0";
            $results = $db->Execute($sql);
            $themeId = $results->fields['template_dir'];
        }

        return $themeId;
    }

    /**
     * Set the configured zen-cart theme id.
     *
     * @param string themeId The theme id.
     * @param int languageId Optional language id; default is <em>0</em> for all.
     */
    function setZCThemeId($themeId, $languageId=0) {
        $db = ZMRuntime::getDB();

        // update or insert?
        $sql = "select *
                from " . TABLE_TEMPLATE_SELECT . "
                where template_language = :languageId";
        $sql = $db->bindVars($sql, ":languageId", $languageId, 'integer');
        $results = $db->Execute($sql);

        $sql = '';
        if (0 < $results->RecordCount()) {
            // update
            $sql = "update " . TABLE_TEMPLATE_SELECT . " set template_dir = :themeId
                    where template_id = :templateId
                    and template_language = :languageId";
            $sql = $db->bindVars($sql, ":themeId", $themeId, 'string');
            $sql = $db->bindVars($sql, ":templateId", $results->fields['template_id'], 'integer');
            $sql = $db->bindVars($sql, ":languageId", $languageId, 'integer');
        } else {
            // insert
            $sql = "insert into " . TABLE_TEMPLATE_SELECT . " (template_dir, template_language)
                    values (:themeId, :languageId)";
            $sql = $db->bindVars($sql, ":themeId", $themeId, 'string');
            $sql = $db->bindVars($sql, ":languageId", $languageId, 'integer');
        }

        $results = $db->Execute($sql);
    }

}

?>
