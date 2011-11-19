<?php
/*
 * ZenMagick - Another PHP framework.
 * Copyright (C) 2006-2011 zenmagick.org
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
//TODO: can we just move them in here?
require_once dirname(__FILE__).DIRECTORY_SEPARATOR.'_zm.php';

use zenmagick\base\Beans;
use zenmagick\base\Runtime;
use zenmagick\base\ZMObject;

use Symfony\Component\Yaml\Yaml;


/**
 * Locale service.
 *
 * <p>Delegates translations to an instance of <code>ZMLocale</code>.</p>
 *
 * <p>The implementation used can be configured via the setting 'zenmagick.core.locales.provider'. If none is
 *  configured, a default echo implementation will be used.</p>
 *
 * @author DerManoMann
 * @package org.zenmagick.core.services.locale
 */
class ZMLocales extends \ZMObject {
    private $locale_;
    private $locales_;


    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
        $this->locale_ = null;
        $this->locales_ = null;
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }

    /**
     * Get instance.
     */
    public static function instance() {
        return Runtime::getContainer()->get('localeService');
    }


    /**
     * Get the locale to be used.
     *
     * <p>The reload flag is mainly to allow applications to switch to a different locale implementation at a later stage during
     * startup.</p>
     *
     * @param boolean reload Optional flag to force a reload; default is <code>false</code>.
     * @param string locale Optional locale to init the locale if a new one is created; default is <code>null</code>.
     * @param string path Optional path to override the default path generation based on the locale name; default is <code>null</code>.
     * @return ZMLocale The locale.
     */
    public function getLocale($reload=false, $locale=null, $path=null) {
        if (null == $this->locale_ || $reload) {
            $this->locale_ = Beans::getBean(Runtime::getSettings()->get('zenmagick.core.locales.provider', 'ZMEchoLocale'));
            if (null !== $locale) {
                $this->locale_->init($locale, $path);
            }
        }

        return $this->locale_;
    }

    /**
     * Get a list of all valid locale codes for the current locale in decreasing order of importance.
     *
     * @return array List of locale codes.
     */
    public function getValidLocaleCodes() {
        $code = $this->getLocale()->getCode();
        $codes = array($code);
        $token = explode('_', $code);
        if (1 < count($token)) {
            $codes[] = $token[0];
        }
        return $codes;
    }

    /**
     * Init locale.
     *
     * @param string locale The locale name, for example: <em>en_NZ</em>.
     * @param string path Optional path to override the default path generation based on the locale name; default is <code>null</code>.
     * @param boolean reload Optional flag to force a reload; default is <code>false</code>.
     */
    public function init($locale, $path=null, $reload=false) {
        $this->getLocale($reload)->init($locale, $path);
    }

    /**
     * Get locales.
     *
     * @return array Map of all available locales with the locale as key and the name as value.
     */
    public function getLocalesList() {
        if (null === $this->locales_) {
            $this->locales_ = array();
            $path = \ZMFileUtils::mkPath(Runtime::getApplicationPath(), 'locale');
            $handle = opendir($path);
            while (false !== ($file = readdir($handle))) {
                $yamlFile = $path.$file.DIRECTORY_SEPARATOR.'locale.yaml';
                if (is_dir($path.$file) && file_exists($yamlFile)) {
                    $yaml = Yaml::parse($yamlFile);
                    if (is_array($yaml)) {
                        $name = array_key_exists('name', $yaml) ? $yaml['name'] : $file;
                        $this->locales_[$file] = $name;
                    }
                }
            }
            closedir($handle);
        }

        return $this->locales_;
    }

}
