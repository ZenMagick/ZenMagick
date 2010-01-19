<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2009 zenmagick.org
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
 * @author DerManoMann
 * @package org.zenmagick.store.services.themes
 * @version $Id$
 */
class ZMTheme extends ZMObject {
    private $themeId_;
    private $themeInfo_;
    private $config_;


    /**
     * Create new instance.
     *
     * @params string themeId The theme id/name.
     */
    function __construct($themeId) {
        parent::__construct();
        $this->themeId_ = $themeId;
        $this->themeInfo_ = null;
        $configFile = $this->getBaseDir().'theme.yaml';
        if (file_exists($configFile)) {
            $this->config_ = ZMRuntime::yamlLoad(file_get_contents($configFile));
        } else {
            $this->config_ = array();
        }
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Get this themes id.
     *
     * @return string The theme id.
     */
    public function getThemeId() { 
        return $this->themeId_;
    }

    /**
     * Return the full filename for the themes base directory.
     *
     * @return string The theme base directory.
     */
    public function getBaseDir() {
        return Runtime::getThemesDir() . $this->themeId_ . DIRECTORY_SEPARATOR;
    }

    /**
     * Get theme config.
     *
     * @param string key Optional config key; default is <code>null</code> to return the full map.
     * @return mixed Theme config map, the value of a specific key or <code>null</code> for unknown keys.
     */
    public function getConfig($key=null) {
        if (null == $key) {
            return $this->config_;
        }

        if (array_key_exists($key, $this->config_)) {
            return $this->config_[$key];
        }

        return null;
    }

    /**
     * Set theme name.
     *
     * @return string The name.
     */
    public function getName() {
        return $this->config_['name'];
    }

    /**
     * Set full theme config.
     *
     * @param array config The new config map.
     */
    public function setConfig($config) {
        $this->config_ = $config;
    }

    /**
     * Set theme config value.
     *
     * @param mixed key The config key or an array to set all.
     * @param mixed value The value.
     */
    public function setConfigValue($key, $value) {
        if (is_array($key)) {
            $this->config_ = $key;
            return;
        }
        $this->config_[$key] = $value;
    }

    /**
     * Set the layout for the given template.
     *
     * @param string template The template.
     * @param string name The layout name.
     */
    public function setLayout($template, $name) { 
        if (!array_key_exists('layout', $this->config_)) {
            $this->setConfigValue('layout', array());
        }
        $this->config_['layout'][$template] = $name;
    }

    /**
     * Get the layout for the given template.
     *
     * @param string template The template.
     * @return string The layout name or <code>null</code>.
     */
    public function getLayoutFor($template) {
        if (array_key_exists($template, $this->config_['layout'])) {
            return $this->config_['layout'][$template];
        } else if (array_key_exists('defaultLayout', $this->config_)) {
            return $this->config_['defaultLayout'];
        }

        // default to no layout
        return null;
    }




    /**
     * Check if a layout is configured for the given page.
     *
     * @param string page The page name.
     * @return boolean <code>true</code> if a layout file is configured for the given page.
     */
    public function hasLayout($page) {
        // layouts reside in the content directory
        return file_exists($this->getContentDir().$page.ZMSettings::get('templateSuffix'));
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
     * @return string An absolute URL.
     */
    public function themeURL($uri) {
        $url = Runtime::getThemesPathPrefix().$this->themeId_."/".'content/'.$uri;
        if (ZMSettings::get('isEnableThemeDefaults') && !file_exists($this->getContentDir().$uri)) {
            if (file_exists(Runtime::getThemesDir().ZMSettings::get('defaultThemeId').DIRECTORY_SEPARATOR.'content'.DIRECTORY_SEPARATOR.$uri)) {
                $url = Runtime::getThemesPathPrefix().ZMSettings::get('defaultThemeId')."/".'content/'.$uri;
            }
        }

        return ZMRequest::instance()->getToolbox()->html->encode($url, false);
    }


    /**
     * Return the path of the extra directory.
     *
     * @return string A full filename denoting the themes extra directory.
     */
    public function getExtraDir() {
        return $this->getBaseDir() . 'extra'.DIRECTORY_SEPARATOR;
    }

    /**
     * Return the path of the boxes directory.
     *
     * @return string A full filename denoting the themes boxes directory.
     */
    public function getBoxesDir() {
        return $this->getBaseDir() . 'content'.DIRECTORY_SEPARATOR.'boxes'.DIRECTORY_SEPARATOR;
    }

    /**
     * Return the path of the content directory.
     *
     * @return string A full filename denoting the themes content directory.
     */
    public function getContentDir() {
        return $this->getBaseDir() . 'content'.DIRECTORY_SEPARATOR;
    }

    /**
     * Return the path of the views directory.
     *
     * @return string A full filename denoting the themes views directory.
     */
    public function getViewsDir() {
        return $this->getBaseDir() . 'content'.DIRECTORY_SEPARATOR.'views'.DIRECTORY_SEPARATOR;
    }

    /**
     * Return the path of the lang directory.
     *
     * @return string A full filename denoting the themes lang directory.
     */
    public function getLangDir() {
        return $this->getBaseDir() . 'lang'.DIRECTORY_SEPARATOR;
    }

    /**
     * Resolve a theme relative filename into a full path.
     *
     * <p>If the file is not found and <code>isEnableThemeDefaults</code> is set to <code>true</code>,
     * the method will try to resolve the name in the default theme.</p>
     *
     * @param string name A theme relative filename.
     * @param string baseDir An optional base directory; default is <code>content/</code>
     * @param boolean echo If <code>true</code>, the URL will be echo'ed as well as returned.
     * @return string A fully qualified filename.
     */
    public function themeFile($name, $baseDir='content/', $echo=false) {
        $file = $this->getBaseDir().$baseDir.$name;
        if (ZMSettings::get('isEnableThemeDefaults') && !file_exists($file)) {
            // check for default
            $dfile = Runtime::getThemesDir().ZMSettings::get('defaultThemeId').DIRECTORY_SEPARATOR.$baseDir.$name;
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
     * @param string baseDir An optional base directory; default is <code>content/</code>
     * @return boolean <code>true</code> if the file exists, <code>false</code> if not.
     */
    public function themeFileExists($name, $baseDir='content/') {
		    return file_exists($this->themeFile($name, $baseDir));
    }

    /**
     * Get a list of available static pages.
     *
     * @param boolean includeDefaults If set to <code>true</code>, default pages will be included; default is <code>false</code>.
     * @param int languageId Optional language id; default is <code>null</code> for current language.
     * @return array List of available static page names.
     */
    public function getStaticPageList($includeDefaults=false, $languageId=null) {
        if (null == $languageId) {
            $session = ZMRequest::instance()->getSession();
            $language = $session->getLanguage();
        } else {
            $language = ZMLanguages::instance()->getLanguageForId($languageId);
        }
        $languageDir = $language->getDirectory();
        $path = $this->getLangDir().$languageDir."/".'static/';

        $pages = array();
        if (is_dir($path)) {
            $handle = @opendir($path);
            while (false !== ($file = readdir($handle))) { 
                if (!ZMLangUtils::endsWith($file, '.php')) {
                    continue;
                }
                $page = str_replace('.php', '', $file);
                $pages[$page] = $page;
            }
            @closedir($handle);
        }

        if ($includeDefaults) {
            $path = Runtime::getThemesDir().ZMSettings::get('defaultThemeId').DIRECTORY_SEPARATOR.'lang'.DIRECTORY_SEPARATOR.$languageDir.DIRECTORY_SEPARATOR.'static'.DIRECTORY_SEPARATOR;
            if (is_dir($path)) {
                $handle = @opendir($path);
                while (false !== ($file = readdir($handle))) { 
                    if (!ZMLangUtils::endsWith($file, '.php')) {
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
    public function saveStaticPageContent($page, $contents, $languageId=null) {
        if (null == $languageId) {
            $session = ZMRequest::instance()->getSession();
            $language = $session->getLanguage();
        } else {
            $language = ZMLanguages::instance()->getLanguageForId($languageId);
        }
        $languageDir = $language->getDirectory();
        $path = $this->getLangDir().$languageDir."/".'static/';
        if (!file_exists($path)) {
            ZMFileUtils::mkdir($path);
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
        ZMFileUtils::setFilePerms($filename);

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
     * @return string The content or <code>null</code>.
     */
    public function staticPageContent($page, $languageId=null) {
        if (null === $languageId) {
            $session = ZMRequest::instance()->getSession();
            $language = $session->getLanguage();
        } else {
            $language = ZMLanguages::instance()->getLanguageForId($languageId);
        }
        $languageDir = $language->getDirectory();
        $path = $this->getLangDir().$languageDir.DIRECTORY_SEPARATOR.'static'.DIRECTORY_SEPARATOR;

        $filename = $path.$page.'.php';
        if (!file_exists($filename) && ZMSettings::get('isEnableThemeDefaults')) {
            $filename = Runtime::getThemesDir().ZMSettings::get('defaultThemeId').DIRECTORY_SEPARATOR.'lang'.DIRECTORY_SEPARATOR.$languageDir.DIRECTORY_SEPARATOR.'static'.DIRECTORY_SEPARATOR.$page.'.php';
        }

        $contents = null;
        if (file_exists($filename)) {
            $contents = file_get_contents($filename);
        }

        // allow PHP
        ob_start();
        eval('?>'.$contents);
        $contents = ob_get_clean();

        return $contents;
    }

    /**
     * Load locale settings (l10n/i18n).
     *
     * <p>NOTE: This is only going to load mappings. However, since i18n
     * settings need to be set using <code>define(..)</code>, this is done
     * in a separate function, once loading (and theme switching) is over.</p>
     *
     * @param ZMLanguage language The language.
     */
    public function loadLocale($language) {
        if (null === $language) {
            // this may happen if the i18n patch hasn't been updated
            $language = Runtime::getDefaultLanguage();
        }
        $path = $this->getLangDir().$language->getDirectory()."/";
        $l10n = $path . "l10n.php";
        if (file_exists($l10n)) {
            require_once($l10n);
        }
        $i18n = $path . "i18n.php";
        if (file_exists($i18n)) {
            require_once($i18n);
        }
    }

}

?>
