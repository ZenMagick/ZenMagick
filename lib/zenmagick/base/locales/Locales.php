<?php
/*
 * ZenMagick - Another PHP framework.
 * Copyright (C) 2006-2012 zenmagick.org
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
namespace zenmagick\base\locales;

use zenmagick\base\Beans;
use zenmagick\base\Runtime;
use zenmagick\base\ZMObject;

use Symfony\Component\Yaml\Yaml;

/**
 * Locale service.
 *
 * <p>Delegates translations to an instance of <code>Locale</code>.</p>
 *
 * @author DerManoMann <mano@zenmagick.org> <mano@zenmagick.org>
 */
class Locales extends ZMObject {
    private $locale_;
    private $localesList;


    /**
     * Create new instance.
     */
    public function __construct() {
        parent::__construct();
        $this->locale_ = null;
        $this->localesList = null;
    }


    /**
     * Get the active locale.
     *
     * <p>The reload flag is mainly to allow applications to switch to a different locale implementation at a later stage during
     * startup.</p>
     *
     * @param boolean reload Optional flag to force a reload; default is <code>false</code>.
     * @param string locale Optional locale to init the locale if a new one is created; default is <code>null</code>.
     * @param string path Optional path to override the default path generation based on the locale name; default is <code>null</code>.
     * @return Locale The locale.
     */
    public function getLocale($reload=false, $locale=null, $path=null) {
        if (null === $this->locale_ || $reload) {
            $this->locale_ = Beans::getBean(Runtime::getSettings()->get('zenmagick.base.locales.handler', 'zenmagick\base\locales\Locale'));
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
        if (null === $this->localesList) {
            $this->localesList = array();
            $path = realpath(Runtime::getInstallationPath()).'/apps/base/locale/';
            $handle = opendir($path);
            while (false !== ($file = readdir($handle))) {
                $yamlFile = $path.$file.'/locale.yaml';
                if (is_dir($path.$file) && file_exists($yamlFile)) {
                    $yaml = Yaml::parse($yamlFile);
                    if (is_array($yaml)) {
                        $name = array_key_exists('name', $yaml) ? $yaml['name'] : $file;
                        $this->localesList[$file] = $name;
                    }
                }
            }
            closedir($handle);
        }

        return $this->localesList;
    }

}
