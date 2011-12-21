<?php
/*
 * ZenMagick - Smart e-commerce
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
namespace zenmagick\base\locales;

use zenmagick\base\Runtime;
use zenmagick\base\Toolbox;
use zenmagick\base\ZMObject;

use Symfony\Component\Yaml\Yaml;

/**
 * Abstract <code>Locale</code>.
 *
 * @author DerManoMann <mano@zenmagick.org> <mano@zenmagick.org>
 * @package zenmagick.base.locales
 */
abstract class Locale extends ZMObject {
    const DEFAULT_DOMAIN = 'defaults';

    private $locale_;
    private $name_;
    private $formats_;


    /**
     * Create new instance.
     */
    public function __construct() {
        $this->locale_ = null;
        $this->name_ = null;
        // absolute defaults for DateTime::format()
        $this->formats_ = array(
            'date' => array(
                'short' => 'd/m/Y',
                'long' => 'D, d M Y'
            ),
            'time' => array(
                'short' => 'H:i:s',
                'long' => 'H:i:s u'
            )
        );
    }


    /**
     * Resolve a locale path.
     *
     * <p>The path given is assumed to contain the full locale as specified in the <code>$locale</code> parameter.</p>
     * <p>The function will validate the path and if not valid will default to using just the language.</p>
     *
     * @param string path The full path.
     * @param string locale The locale.
     * @return string A valid path or <code>null</code>.
     *
     */
    public static function resolvePath($path, $locale) {
        if (file_exists($path)) {
            return $path;
        }

        $lt = explode('_', $locale);
        if (2 > count($lt)) {
            return null;
        }

        // try language
        $path = str_replace($locale, $lt[0], $path);
        if (file_exists($path)) {
            return $path;
        }

        return null;
    }

    /**
     * Get the locale code.
     *
     * @return string The locale code.
     */
    public function getCode() {
        return $this->locale_;
    }

    /**
     * Get the locale name.
     *
     * @return string The name.
     */
    public function getName() {
        return $this->name_;
    }

    /**
     * Add resource.
     *
     * @param mixed resource The resource to add.
     * @param string locale The locale to be used in the form: <code>[language code]_[country code]</code> or just <code>[language code]</code>;
     *  for exampe <em>de_DE</em>, <em>en_NZ</em> or <em>es</code>; default is <code>null</code> to use the current locale.
     * @param string domain The translation domain; default is <code>Locale::DEFAULT_DOMAIN</code>.
     */
    public abstract function addResource($resource, $locale=null, $domain=Locale::DEFAULT_DOMAIN);

    /**
     * Init locale.
     *
     * <p>Init the configured locale implementation. This includes creating the singleton instance of the locale and calling <code>init($locale)</code>
     * on the locale instance.</p>
     *
     * <p>The locale instance, in turn, will typically try to load the default language mappings for the locale/language given. Depending
     * on the actual implementation used this can be a file (yaml, mo) or just a static map kept in memory.</p>
     *
     * @param string locale The locale to be used in the form: <code>[language code]_[country code]</code> or just <code>[language code]</code>;
     *  for exampe <em>de_DE</em>, <em>en_NZ</em> or <em>es</code>.
     * @param string path Optional path to override the default path generation based on the locale name; default is <code>null</code>.
     * @return array Two element array with path and 'locale.yaml' content (as yaml) as data.
     */
    public function init($locale, $path=null) {
        $token = explode('_', $locale);
        if (false == setlocale(LC_ALL, $locale)) {
            // try first token
            setlocale(LC_ALL, $token[0]);
        }

        $this->locale_ = $locale;
        $this->name_ = $locale;

        if (null == $path) {
            $path = realpath(Runtime::getApplicationPath()).'/locale/'.$locale;
            if (null == ($path = Locale::resolvePath($path, $locale))) {
                Runtime::getLogging()->debug('unable to resolve path for locale = "'.$locale.'"');
                return null;
            }
        }

        $yaml = array();
        $filename = realpath($path).'/locale.yaml';
        if (!empty($filename) && file_exists($filename)) {
            $yaml = Yaml::parse($filename);
            if (is_array($yaml)) {
                if (array_key_exists('name', $yaml)) {
                    $this->name_ = $yaml['name'];
                }
                if (array_key_exists('formats', $yaml)) {
                    $this->formats_ = Toolbox::arrayMergeRecursive($this->formats_, $yaml['formats']);
                }
            }
        }

        return array($path, $yaml);
    }

    /**
     * Translate the given text.
     *
     * @param string text The text to translate.
     * @param mixed context Optional translation context; default is <code>null</code>.
     * @param string domain The translation domain; default is <code>Locale::DEFAULT_DOMAIN</code>.
     * @return string The translated text.
     */
    public abstract function translate($text, $context=null, $domain=Locale::DEFAULT_DOMAIN);

    /**
     * Translate the given text with plural option.
     *
     * @param string single The text to translate for single case.
     * @param int number The number.
     * @param string plural The text to translate for plural case; default is <code>null</code> to default to the single case.
     * @param mixed context Optional translation context; default is <code>null</code>.
     * @param string domain The translation domain; default is <code>Locale::DEFAULT_DOMAIN</code>.
     * @return string The translated text or, if no translation found, the original text.
     */
    public abstract function translatePlural($single, $number, $plural=null, $context=null, $domain=Locale::DEFAULT_DOMAIN);

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
        if (array_key_exists($group, $this->formats_)) {
            if (null == $type) {
                return $this->formats_[$group];
            } else if (array_key_exists($type, $this->formats_[$group])) {
                return $this->formats_[$group][$type];
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
        $this->formats_ = Toolbox::arrayMergeRecursive($this->formats_, $formats);
    }

}
