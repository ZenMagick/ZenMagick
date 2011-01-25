<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2010 zenmagick.org
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
 * @package zenmagick.store.shared.services.themes
 */
class ZMTheme extends ZMObject {
    private $themeId_;
    private $config_;


    /**
     * Create new instance.
     *
     * @params string themeId The theme id/name.
     */
    function __construct($themeId) {
        parent::__construct();
        $this->themeId_ = $themeId;
        $configFile = $this->getBaseDir().'theme.yaml';
        if (file_exists($configFile)) {
            $this->config_ = ZMRuntime::yamlParse(file_get_contents($configFile));
        } else {
            $this->config_ = array();
            //XXX: try for zc theme
            $templatePath = ZMFileUtils::mkPath(DIR_FS_CATALOG, 'includes', 'templates', $themeId);
            if (is_dir($templatePath) && file_exists($templatePath.'template_info.php')) {
                include $templatePath.'template_info.php';
                if (isset($template_name)) {
                    $this->config_['name'] = $template_name.' (Zen Cart)';
                    $this->config_['version'] = $template_version;
                    $this->config_['author'] = $template_author;
                    $this->config_['description'] = $template_description;
                    $this->config_['zencart'] = true;
                }
            }
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
        return array_key_exists('name', $this->config_) ? $this->config_['name'] : '??';
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
     * Get a list of available static pages.
     *
     * @param boolean includeDefaults If set to <code>true</code>, default pages will be included; default is <code>false</code>.
     * @param int languageId Language id.
     * @return array List of available static page names.
     */
    public function getStaticPageList($includeDefaults=false, $languageId) {
        $language = ZMLanguages::instance()->getLanguageForId($languageId);
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
            $path = Runtime::getThemesDir().ZMSettings::get('apps.store.themes.default').DIRECTORY_SEPARATOR.'lang'.DIRECTORY_SEPARATOR.$languageDir.DIRECTORY_SEPARATOR.'static'.DIRECTORY_SEPARATOR;
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
     * @param int languageId Language id.
     * @return boolean The status.
     */
    public function saveStaticPageContent($page, $contents, $languageId) {
        $language = ZMLanguages::instance()->getLanguageForId($languageId);
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
     * @param string page The page name.
     * @param int languageId Language id.
     * @return string The content or <code>null</code>.
     */
    public function staticPageContent($page, $languageId) {
        $language = ZMLanguages::instance()->getLanguageForId($languageId);
        $languageDir = $language->getDirectory();
        $path = $this->getLangDir().$languageDir.DIRECTORY_SEPARATOR.'static'.DIRECTORY_SEPARATOR;

        $filename = $path.$page.'.php';
        if (!file_exists($filename)) {
            return null;
        }

        $contents = @file_get_contents($filename);
        // allow PHP
        ob_start();
        eval('?>'.$contents);
        return ob_get_clean();
    }

    /**
     * Load locale settings (l10n/i18n).
     *
     * @param ZMLanguage language The language.
     */
    public function loadLocale($language) {
        if (null === $language) {
            // this may happen if the i18n patch hasn't been updated
            $language = Runtime::getDefaultLanguage();
        }
        $path = $this->getLangDir().$language->getDirectory().DIRECTORY_SEPARATOR;
        // re-init with next file
        ZMLocales::instance()->getLocale()->init(ZMSettings::get('zenmagick.core.locales.locale'), $path);
    }

    /**
     * Load additional theme config settins from <em>theme.yaml</em>.
     */
    public function loadSettings() {
        if (null !== ($localeSettings = $this->getConfig('locale'))) {
            $locale = ZMLocales::instance()->getLocale();
            foreach (ZMLocales::instance()->getValidLocaleCodes() as $code) {
                if (array_key_exists($code, $localeSettings)) {
                    $locale->setFormats($localeSettings[$code]);
                }
            }
        }

        // add optional url mappings
        $urls = $this->getConfig('urls');
        if ($urls && is_array($urls)) {
            // merge
            ZMUrlManager::instance()->setMappings($urls, false);
        }

        // add optional settings
        $settings = $this->getConfig('settings');
        if ($settings && is_array($settings)) {
            // merge
            ZMSettings::addAll($settings, true);
        }
    }

}
