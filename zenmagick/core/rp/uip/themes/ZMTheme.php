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
 * A theme.
 *
 * @author mano
 * @package org.zenmagick.rp.uip.themes
 * @version $Id$
 */
class ZMTheme extends ZMObject {
    var $themeId_;
    var $themeInfo_;


    /**
     * Default c'tor.
     *
     * @params string themeId The theme id/name.
     */
    function ZMTheme($themeId) {
        parent::__construct();

        $this->themeId_ = $themeId;
        $this->themeInfo_ = null;
    }

    /**
     * Default c'tor.
     *
     * @params string themeId The theme id/name.
     */
    function __construct($themeId) {
        $this->ZMTheme($themeId);
    }

    /**
     * Default d'tor.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Get this themes id.
     *
     * @return string The theme id.
     */
    function getThemeId() { return $this->themeId_; }

    /**
     * Check if a layout is configured for the given page.
     *
     * @param string page The page name.
     * @return boolean <code>true</code> if a layout file is configured for the given page.
     */
    function hasLayout($page) {
        // layouts reside in the content directory
        return file_exists($this->getContentDir().$page.zm_setting('templateSuffix'));
    }

    /**
     * Resolve a theme relative URI.
     *
     * <p>The given <code>uri</code> is assumed to be relative to the themes <em>content</em> folder.</p>
     *
     * <p>If the file is not found and <code>isEnableThemeDefaults</code> is set to <code>true</code>,
     * the method will try to resolve the name in the default theme.</p>
     *
     * @param string uri The relative URI.
     * @param boolean echo If <code>true</code>, the URL will be echo'ed as well as returned.
     * @return string An absolute URL.
     */
    function themeURL($uri, $echo=ZM_ECHO_DEFAULT) {
    global $zm_runtime;

        $url = $zm_runtime->getThemesPathPrefix().$this->themeId_."/".ZM_THEME_CONTENT_DIR.$uri;
        if (zm_setting('isEnableThemeDefaults') && !file_exists($this->getContentDir().$uri)) {
            if (file_exists($zm_runtime->getThemesDir().ZM_DEFAULT_THEME.'/'.ZM_THEME_CONTENT_DIR.$uri)) {
                $url = $zm_runtime->getThemesPathPrefix().ZM_DEFAULT_THEME."/".ZM_THEME_CONTENT_DIR.$uri;
            }
        }

        $url = zm_htmlurlencode($url);

		if ($echo) echo $url;
		return $url;
    }


    /**
     * Return the full filename for the themes root directory.
     *
     * @return string The themes root directory.
     */
    function getRootDir() {
    global $zm_runtime;

        return $zm_runtime->getThemesDir() . $this->themeId_ . '/';
    }

    /**
     * Return the path of the extra directory.
     *
     * @return string A full filename denoting the themes extra directory.
     */
    function getExtraDir() {
        return $this->getRootDir() . ZM_THEME_EXTRA_DIR;
    }

    /**
     * Return the path of the boxes directory.
     *
     * @return string A full filename denoting the themes boxes directory.
     */
    function getBoxesDir() {
        return $this->getRootDir() . ZM_THEME_BOXES_DIR;
    }

    /**
     * Return the path of the content directory.
     *
     * @return string A full filename denoting the themes content directory.
     */
    function getContentDir() {
        return $this->getRootDir() . ZM_THEME_CONTENT_DIR;
    }

    /**
     * Return the path of the views directory.
     *
     * @return string A full filename denoting the themes views directory.
     */
    function getViewsDir() {
        $themeInfo = $this->getThemeInfo();
        return $themeInfo->getViewsDir();
    }

    /**
     * Return the path of the lang directory.
     *
     * @return string A full filename denoting the themes lang directory.
     */
    function getLangDir() {
        return $this->getRootDir() . ZM_THEME_LANG_DIR;
    }

    /**
     * Resolve a theme relative filename into a full path.
     *
     * <p>If the file is not found and <code>isEnableThemeDefaults</code> is set to <code>true</code>,
     * the method will try to resolve the name in the default theme.</p>
     *
     * @param string name A theme relative filename.
     * @param string baseDir An optional base directory; default is <code>ZM_THEME_CONTENT_DIR</code>
     * @param boolean echo If <code>true</code>, the URL will be echo'ed as well as returned.
     * @return string A fully qualified filename.
     */
    function themeFile($name, $baseDir=ZM_THEME_CONTENT_DIR, $echo=false) {
    global $zm_runtime;

        $file = $this->getRootDir().$baseDir.$name;
        if (zm_setting('isEnableThemeDefaults') && !file_exists($file)) {
            // check for default
            $dfile = $zm_runtime->getThemesDir().ZM_DEFAULT_THEME.'/'.$baseDir.$name;
            if (file_exists($dfile)) {
                $file = $dfile;
            }
        }

	    if ($echo) echo $file;
        return $file;
    }

    /**
     * Check if the given theme relative file exists.
     *
     * @param string name A theme relative filename.
     * @param string baseDir An optional base directory; default is <code>ZM_THEME_CONTENT_DIR</code>
     * @return boolean <code>true</code> if the file exists, <code>false</code> if not.
     */
    function themeFileExists($name, $baseDir=ZM_THEME_CONTENT_DIR) {
		    return file_exists($this->themeFile($name, $baseDir));
    }

    /**
     * Get a list of available static pages.
     *
     * @param boolean includeDefaults If set to <code>true</code>, default pages will be included; default is <code>false</code>.
     * @param int languageId Optional language id; default is <code>null</code> for current language.
     * @return array List of available static page names.
     */
    function getStaticPageList($includeDefaults=false, $languageId=null) {
    global $zm_runtime, $zm_request, $zm_languages;

        if (null == $languageId) {
            $session = $zm_request->getSession();
            $language = $session->getLanguage();
        } else {
            $language = $zm_languages->getLanguageForId($languageId);
        }
        $languageDir = $language->getDirectory();
        $path = $this->getLangDir().$languageDir."/".ZM_THEME_STATIC_DIR;

        $pages = array();
        if (is_dir($path)) {
            $handle = @opendir($path);
            while (false !== ($file = readdir($handle))) { 
                if (!zm_ends_with($file, '.php')) {
                    continue;
                }
                $page = str_replace('.php', '', $file);
                $pages[$page] = $page;
            }
            @closedir($handle);
        }

        if ($includeDefaults) {
            $path = $zm_runtime->getThemesDir().ZM_DEFAULT_THEME.'/'.ZM_THEME_LANG_DIR.$languageDir."/".ZM_THEME_STATIC_DIR;
            if (is_dir($path)) {
                $handle = @opendir($path);
                while (false !== ($file = readdir($handle))) { 
                    if (!zm_ends_with($file, '.php')) {
                        continue;
                    }
                    $page = str_replace('.php', '', $file);
                    $pages[$page] = $page;
                }
                @closedir($handle);
            }
        }
        return $pages;
    }

    /**
     * Write the content of a static (define) page.
     *
     * @param string page The page name.
     * @param string contents The contents.
     * @param int languageId Optional language id; default is <code>null</code> for current language.
     * @return boolean The status.
     */
    function saveStaticPageContent($page, $contents, $languageId=null) {
    global $zm_runtime, $zm_request, $zm_languages;

        if (null == $languageId) {
            $session = $zm_request->getSession();
            $language = $session->getLanguage();
        } else {
            $language = $zm_languages->getLanguageForId($languageId);
        }
        $languageDir = $language->getDirectory();
        $path = $this->getLangDir().$languageDir."/".ZM_THEME_STATIC_DIR;
        if (!file_exists($path)) {
            zm_mkdir($path);
        }
        $filename = $path.$page.'.php';

        if (file_exists($filename)) {
            if (file_exists($filename.'.bak')) {
                @unlink($filename.'.bak');
            }
            @rename($filename, $filename.'.bak');
        }
        $handle = fopen($filename, 'w');
        fwrite($handle, $contents, strlen($contents));
        fclose($handle);

        return file_exists($filename);
    }

    /**
     * Get the content of a static (define) page.
     *
     * <p>If the file is not found and <code>isEnableThemeDefaults</code> is set to <code>true</code>,
     * the method will try to resolve the name in the default theme.</p>
     *
     * @param string page The page name.
     * @param int languageId Optional language id; default is <code>null</code> for current language.
     * @param boolean echo If <code>true</code>, the URL will be echo'ed as well as returned.
     * @return string The content or <code>null</code>.
     */
    function staticPageContent($page, $languageId=null, $echo=ZM_ECHO_DEFAULT) {
    global $zm_runtime, $zm_request, $zm_languages;

        if (!zm_setting('isZMDefinePages')) {
            return $this->zcStaticPageContent($page, $languageId, $echo);
        }

        if (null == $languageId) {
            $session = $zm_request->getSession();
            $language = $session->getLanguage();
        } else {
            $language = $zm_languages->getLanguageForId($languageId);
        }
        $languageDir = $language->getDirectory();
        $path = $this->getLangDir().$languageDir."/".ZM_THEME_STATIC_DIR;

        $filename = $path.$page.'.php';
        if (!file_exists($filename) && zm_setting('isEnableThemeDefaults')) {
            $filename = $zm_runtime->getThemesDir().ZM_DEFAULT_THEME.'/'.ZM_THEME_LANG_DIR.$languageDir."/".ZM_THEME_STATIC_DIR.$page.'.php';
        }

        $contents = null;
        if (file_exists($filename)) {
            $contents = file_get_contents($filename);
        }

        if ($echo && null !== $contents) echo $contents;
        return $contents;
    }

    /**
     * Get the content of a static (define) page using the zen-cart location.
     *
     * <p>If the file is not found and <code>isEnableThemeDefaults</code> is set to <code>true</code>,
     * the method will try to resolve the name in the default theme.</p>
     *
     * @param string page The page name.
     * @param int languageId Optional language id; default is <code>null</code> for current language.
     * @param boolean echo If <code>true</code>, the URL will be echo'ed as well as returned.
     * @return string The content or <code>null</code>.
     */
    function zcStaticPageContent($page, $languageId=null, $echo=ZM_ECHO_DEFAULT) {
    global $zm_runtime, $zm_request, $zm_languages;

        if (null == $languageId) {
            $session = $zm_request->getSession();
            $language = $session->getLanguage();
        } else {
            $language = $zm_languages->getLanguageForId($languageId);
        }
        $languageDir = $language->getDirectory();
        $filename = DIR_WS_LANGUAGES . $languageDir . '/html_includes/'.$zm_runtime->getZCThemeId().'/define_' . $page . '.php';
        if (!file_exists($filename) && zm_setting('isEnableThemeDefaults')) {
            $filename = DIR_WS_LANGUAGES . $languageDir . '/html_includes/define_' . $page . '.php';
        }

        $contents = null;
        if (file_exists($filename)) {
            $contents = file_get_contents($filename);
        }

        if ($echo && null !== $contents) echo $contents;
        return $contents;
    }

    /**
     * Load and <code>eval</code> a theme file.
     *
     * <p>This allows to use <em>PHP</em> code in, for example, JavaScript files. One side-effect is
     * that the evaluated content is inline'ed rather than loaded from a separate file. That
     * means it's more usefule for small snippets rather than large files</p>
     *
     * @param string file The filename.
     * @return string The eval'ed content.
     */
    function themeFileContents($file) {
        return eval('?>'.file_get_contents($this->themeFile($file)));
    }

    /**
     * Get the <code>ZMThemeInfo</code> for this theme.
     *
     * @return ZMThemeInfo A <code>ZMThemeInfo</code> instance.
     */
    function getThemeInfo() {
    global $zm_runtime;

        if (null == $this->themeInfo_) {
            $this->themeInfo_ = $zm_runtime->getThemeInfoForId($this->themeId_);
        }

        return $this->themeInfo_;
    }

}

?>
