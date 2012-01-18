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

use zenmagick\base\Runtime;

/**
 * Locale utils.
 *
 * @author DerManoMann <mano@zenmagick.org> <mano@zenmagick.org>
 * @package org.zenmagick.core.utils
 */
class ZMLocaleUtils {

    /**
     * Format a date as short.
     *
     * @param Date date A date.
     * @param string format Optional format string to override the format provided by the active <code>Locale</code>; default is <code>null</code>.
     * @return string A short version.
     */
    public static function dateShort($date, $format=null) {
        if ($date instanceof DateTime) {
            $format = null != $format ? $format : Runtime::getContainer()->get('localeService')->getLocale()->getFormat('date', 'short');
            return $date->format($format);
        }

        return $date;
    }

    /**
     * Format a date as long.
     *
     * @param Date date A date.
     * @param string format Optional format string to override the format provided by the active <code>Locale</code>; default is <code>null</code>.
     * @return string A long version.
     */
    public static function dateLong($date, $format=null) {
        if ($date instanceof DateTime) {
            $format = null != $format ? $format : Runtime::getContainer()->get('localeService')->getLocale()->getFormat('date', 'long');
            return $date->format($format);
        }

        return $date;
    }

    /**
     * Convenience method to lookup a locale format.
     *
     * @param string group The group.
     * @param string type Optional type.
     * @return string The format or <code>null</code>
     * @see Locale::getFormat(string,string)
     */
    public static function getFormat($group, $type=null) {
        return Runtime::getContainer()->get('localeService')->getLocale()->getFormat($group, $type);
    }

}
