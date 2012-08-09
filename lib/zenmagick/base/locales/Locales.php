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

use DateTime;
use zenmagick\base\Beans;
use zenmagick\base\Runtime;
use zenmagick\base\Toolbox;
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
    private $locale;
    private $loader;
    private $formats;

    /**
     * Create new instance.
     */
    public function __construct() {
        parent::__construct();
        $this->locale = null;
        $this->loader = null;
        $this->formats = array(
            'date' => array(
                'short' => 'd/m/Y',
                'short-ui-format' => 'dd/mm/yy',
                'short-ui-example' => '16/11/67',
                'long' => 'D, d M Y'
            ),
            'time' => array(
                'short' => 'H:i:s',
                'long' => 'H:i:s u'
            ),
            'dir' => 'ltr'
        );
    }

    /**
     * Add resource.
     *
     * @param mixed resource The resource to add.
     * @param string locale The locale to be used in the form: <code>[language code]_[country code]</code> or just <code>[language code]</code>;
     *  for exampe <em>de_DE</em>, <em>en_NZ</em> or <em>es</code>; default is <code>null</code> to use the current locale.
     * @param string domain The translation domain; default is <code>null</code>.
     */
    public function addResource($resource, $locale=null, $domain=null) {
        $this->getLocale()->addResource($resource, $locale, $domain);
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
        if (empty($locale) && !empty($this->locale)) {
            $locale = $this->locale;
        }
        if (null === $this->loader || $reload) {
            $this->loader = new \zenmagick\base\locales\handler\PomoLocale;
            if (null !== $locale) {
                $this->loader->setDefaultDomain('messages');
                $this->loader->init($locale, $path);
            }
        }
        if (empty($this->formats) || ($locale != $this->locale)) {
            $this->initFormats($locale);
        }
        return $this->loader;
    }

    /**
     * Get a list of all valid locale codes for the current locale in decreasing order of importance.
     *
     * @return array List of locale codes.
     */
    public function getValidLocaleCodes() {
        $code = $this->getLocale()->getLocale();
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
        $this->getLocale($reload, $locale)->init($locale, $path);
    }

    /**
     * Load the formats for a particular locale.
     *
     * @param string locale
     */
    public function initFormats($locale = '') {
        $yaml = array();
        $ypath = realpath(Runtime::getInstallationPath()).'/apps/base/locale/'.$locale;
        $filename = realpath($ypath).'/locale.yaml';
        if (file_exists($filename)) {
            $yaml = Yaml::parse($filename);
            if (is_array($yaml)) {
                if (array_key_exists('formats', $yaml)) {
                    $this->formats = Toolbox::arrayMergeRecursive($this->formats, $yaml['formats']);
                }
            }
        } else {
            Runtime::getLogging()->debug('unable to resolve path for locale = "'.$locale.'"');
        }

    }

    /**
     * Get a format.
     *
     * <p>Formats can be anything that should be handled different for different languages/locale. The <code>type</code> is optional and
     * only required if the <code>group</code> has subgroups.</p>
     *
     * <p>The date/time related format strings are expected to be used in conjunction with the <code>DateTime</code> class.</p>
     *
     * <p>Predefined groups/types are:</p>
     * <ul>
     *  <li><p>date</p>
     *    <ul>
     *      <li>short - a short date</li>
     *      <li>long - a long date</li>
     *    </ul>
     *  </li>
     *  <li><p>time</p>
     *    <ul>
     *      <li>short - a short time</li>
     *      <li>long - a long time</li>
     *    </ul>
     *  </li>
     * </ul>
     *
     * @param string group The format group.
     * @param string type The subtype if required; default is <code>null</code>.
     * @return string A format string or <code>null</code>.
     */
    public function getFormat($group, $type=null) {
        if (array_key_exists($group, $this->formats)) {
            if (null == $type) {
                return $this->formats[$group];
            } else if (array_key_exists($type, $this->formats[$group])) {
                return $this->formats[$group][$type];
            }
        }
        return null;
    }

    /**
     * Set formats.
     *
     * <p>Merge additional formats into this locale.</p>
     *
     * @param array formats Nested map of format definitions.
     */
    public function setFormats($formats) {
        $this->formats = Toolbox::arrayMergeRecursive($this->formats, $formats);
    }

    /**
     * Format a date as short date according to this locales format.
     *
     * @param DateTime date A date.
     * @return string A short version.
     */
    public function shortDate($date) {
        if ($date instanceof DateTime) {
            return $date->format($this->getFormat('date', 'short'));
        }

        return $date;
    }

    /**
     * Format a date as long date according to this locales format.
     *
     * @param DateTime date A date.
     * @return string A long version.
     */
    public function longDate($date) {
        if ($date instanceof DateTime) {
            return $date->format($this->getFormat('date', 'long'));
        }
    }

}
