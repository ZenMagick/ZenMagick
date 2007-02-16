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
 * Theme handling.
 *
 * @author mano
 * @package net.radebatz.zenmagick.rp.uip.themes
 * @version $Id$
 */
class ZMTheme extends ZMObject {

    /**
     * Default c'tor.
     */
    function ZMTheme() {
        parent::__construct();
    }

    /**
     * Default c'tor.
     */
    function __construct() {
        $this->ZMTheme();
    }

    /**
     * Default d'tor.
     */
    function __destruct() {
        parent::__destruct();
    }


    function isValidRequest() {
    global $zm_runtime, $zm_request;

        $themeInfo = $this->getThemeInfo();
        $view = $this->themeFile($themeInfo->getViewDir().$zm_request->getPageName().".php");
        return $this->hasTemplate() || file_exists($view);
    }


    // checks, if a template exists for the current request
    function hasTemplate($name=null) {
    global $zm_request;

        $name = null == $name ? $zm_request->getPageName() : $name;
        return file_exists($this->getThemePath($name.'.php'));
    }


    // resolve theme relative uri
    function themeURL($uri, $echo=true) {
    global $zm_runtime;

        $url = $zm_runtime->getThemeBaseURI().$zm_runtime->getThemeId()."/".ZM_THEME_CONTENT.$uri;
        if (zm_setting('isEnableThemeDefaults') && !file_exists($zm_runtime->getThemeContentPath().$uri)) {
            // check for default
            if (file_exists($zm_runtime->getThemeContentPath('default').$uri)) {
                $url = $zm_runtime->getThemeBaseURI().'default'."/".ZM_THEME_CONTENT.$uri;
            }
        }

        $url = zm_htmlurlencode($url);

		    if ($echo) echo $url;
		    return $url;
    }


    // get the full template path
    function getThemePath($name) {
    global $zm_runtime;

        $file = $zm_runtime->getThemeContentPath().$name;
        if (zm_setting('isEnableThemeDefaults') && !file_exists($file)) {
            // check for default
            $dfile = $zm_runtime->getThemeContentPath('default').$name;
            if (file_exists($dfile)) {
                // update only if found - otherwise leave original filename for easier error analysis
                $file = $dfile;
            }
        }

        return $file;
    }


    // resolve theme relative filename
    function themeFile($name, $echo=false) {
        $file = $this->getThemePath($name);

		    if ($echo) echo $file;
		    return $file;
    }


    // resolve theme relative filename and check for existence
    function themeFileExists($name) {
		    return file_exists($this->themeFile($name, false));
    }


    // get contents of a theme file
    function getThemeFileContents($name, $echo=true) {
        if (!$this->themeFileExists($name))
            return "";

		    $contents = file_get_contents($this->themeFile($name));

        if ($echo) echo $contents;
		    return $contents;
    }


    // include static page content
    function includeStaticPageContent($page, $echo=true) {
    global $zm_request, $zm_runtime;

        $language = $zm_request->getLanguageName();
        $filename = DIR_WS_LANGUAGES . $language . '/html_includes/'.$zm_runtime->getRawThemeId().'/define_' . $page . '.php';
        if (!file_exists($filename)) {
            $filename = DIR_WS_LANGUAGES . $language . '/html_includes/define_' . $page . '.php';
        }

        $contents = file_get_contents($filename);

        if ($echo) echo $contents;
        return $contents;
    }


    // get a list of all themes extra files to be included
    function getExtraFiles() {
    global $zm_runtime;

        return zm_find_includes($zm_runtime->getThemePath()."/" . ZM_THEME_EXTRA, true);
    }


    // get all theme names
    function getThemeDirList() {
    global $zm_runtime;

        $themes = array();
        $handle = @opendir($zm_runtime->getThemeBasePath());
        while (false !== ($file = readdir($handle))) { 
            if ("." == $file || ".." == $file || "CVS" == $file)
                continue;
            array_push($themes, $file);
        }
        @closedir($handle);
        return $themes;
    }


    // get current theme info
    function getThemeInfo($themeId=null) {
    global $zm_runtime;

        // theme id
        $themeId = null == $themeId ? $zm_runtime->getThemeId() : $themeId;
        // theme base path
        $basePath = $zm_runtime->getThemeBasePath();
        $infoName = $themeId. ' ThemeInfo';
        // theme info class name
        $infoClass = zm_mk_classname($infoName);
        // theme info file name
        $infoFile = $basePath.$themeId."/".$infoClass.".php";
        // load
        require_once($infoFile);
        // create instance
        $obj = new $infoClass();
        $obj->setThemeId($themeId);
        return $obj;
    }


    // get theme list
    function getThemeInfoList() {
    global $zm_runtime;

        $infoList = array();
        $basePath = $zm_runtime->getThemeBasePath();
        $dirs = $this->getThemeDirList();
        // load info classes and get instance
        foreach ($dirs as $dir) {
            // assuming that directory name corresponds with theme id
            array_push($infoList, $this->getThemeInfo($dir));
        }

        return $infoList;
    }

}

?>
