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
namespace ZenMagick\Base\Locales;

use ZenMagick\Base\Toolbox;
use ZenMagick\Base\ZMObject;

/**
 * Locale service.
 *
 * @todo use the IntlDateFormmatter for this.
 * @todo use something other than the locale to pick the format
 * @author DerManoMann <mano@zenmagick.org> <mano@zenmagick.org>
 */
class Locales extends ZMObject
{
    private $locale;
    private $loader;
    private $formats;

    /**
     * Create new instance.
     *
     * @param string locale The locale
     */
    public function __construct($locale)
    {
        parent::__construct();
        $this->locale = $locale;
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
        );
        if ('en_US' == $locale) {
            $this->formats = array(
                'date' => array(
                    'short' =>  'm/d/Y',
                    'short-ui-format' => 'mm/dd/yy',
                    'short-ui-example' => '11/16/67',
                    'long' => 'm/d/Y'
                ),
                'time' => array(
                    'short' => 'H:i:s',
                    'long' => 'H:i:s u'
                ),
            );
        }
    }

    /**
     * Set locale
     *
     * @param string locale
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
    }

    /**
     * Get a list of all valid locale codes for the current locale in decreasing order of importance.
     *
     * @return array List of locale codes.
     */
    public function getValidLocaleCodes()
    {
        $code = $this->locale;
        $codes = array($code);
        $token = explode('_', $code);
        if (1 < count($token)) {
            $codes[] = $token[0];
        }

        return $codes;
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
    public function getFormat($group, $type=null)
    {
        if (array_key_exists($group, $this->formats)) {
            if (null == $type) {
                return $this->formats[$group];
            } elseif (array_key_exists($type, $this->formats[$group])) {
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
    public function setFormats($formats)
    {
        $this->formats = Toolbox::arrayMergeRecursive($this->formats, $formats);
    }

    /**
     * Format a date as short date according to this locales format.
     *
     * @param DateTime date A date.
     * @return string A short version.
     */
    public function shortDate($date)
    {
        if ($date instanceof \DateTime) {
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
    public function longDate($date)
    {
        if ($date instanceof \DateTime) {
            return $date->format($this->getFormat('date', 'long'));
        }
    }
}
