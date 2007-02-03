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


/**
 * A central place for all runtime stuff.
 *
 * <p>This is not really neccessary, but I prefer to have a single place
 * where I can look up stuff rather than search tons fo files.</p>
 *
 * @author mano
 * @package net.radebatz.zenmagick
 * @version $Id$
 */
class ZMRuntime {
    var $themeId_;
    var $dbThemeId_;


    /**
     * Default c'tor.
     */
    function ZMRuntime() {
        // init with defaults
        $this->themeId_ = null;
        $this->dbThemeId_ = null;
    }

    /**
     * Default c'tor.
     */
    function __construct() {
        $this->ZMRuntime();
    }

    /**
     * Default d'tor.
     */
    function __destruct() {
    }


    function getDB() { global $db; return $db; }
    function getControllerPath() { return DIR_FS_CATALOG.ZM_CONTROLLER_PATH; }
    function getThemePath($themeId=null) { return $this->getThemeBasePath().((null!=$themeId||!zm_setting('isEnableThemeDefaults'))?$themeId:$this->getThemeId()); }
    function getThemeContentPath($themeId=null) { return $this->getThemePath($themeId)."/".ZM_THEME_CONTENT; }
    function getThemeBasePath() { return DIR_FS_CATALOG.ZM_THEME_BASE_PATH; }
    function getThemeBaseURI() { return $this->getApplicationRoot().ZM_THEME_BASE_PATH; }
    function getThemeLangPath() { return $this->getThemeBasePath().$this->getThemeId()."/lang/"; }
    function getThemeBoxPath($themeId=null) { return $this->getThemePath($themeId)."/".ZM_THEME_BOXES; }
    function getZMRootPath() {  return DIR_FS_CATALOG.ZM_ROOT; }
    function getApplicationRoot() { return DIR_WS_CATALOG; }

    function setThemeId($themeId) { $this->themeId_ = $themeId; }
    function getRawThemeId() {
        $themeId = $this->themeId_;
        //TODO: themes
        if (null == $themeId) {
            if (null == $this->dbThemeId_) {
                $db = $this->getDB();
                $template_dir = "";
                $sql = "select template_dir
                        from " . TABLE_TEMPLATE_SELECT . "
                        where template_language = 0";
                $template_query = $db->Execute($sql);
                $themeId = $template_query->fields['template_dir'];

                $sql = "select template_dir
                        from " . TABLE_TEMPLATE_SELECT . "
                        where template_language = '" . $_SESSION['languages_id'] . "'";
                $template_query = $db->Execute($sql);
                if ($template_query->RecordCount() > 0) {
                    $themeId = $template_query->fields['template_dir'];
                }
                $this->dbThemeId_ = $themeId;
            }
            $themeId = $this->dbThemeId_;
        }
        return $themeId;
    }

    // get the (valid) theme id
    function getThemeId() {
        $themeId = strtolower($this->getRawThemeId());
        $path = $this->getThemeBasePath().$themeId;
        if (!@file_exists($path) || !@is_dir($path)) {
            //error_log("ZenMagick: invalid theme id: '".$zm_runtime->getThemeId().'"');
            return "default";
        }
        return $themeId;
    }

    function isBBActive() {
    global $phpBB;
        return $phpBB->phpBB['db_installed_config'] && $phpBB->phpBB['files_installed'];
    }

    // reconnect; used when switching between databases
    function reconnectDB() {
        $db = $this->getDB();
        $db->selectdb(DB_DATABASE);
	      $db->close();
	      $db->connect(DB_SERVER, DB_SERVER_USERNAME, DB_SERVER_PASSWORD, DB_DATABASE, USE_PCONNECT, false);
    }

}

?>
