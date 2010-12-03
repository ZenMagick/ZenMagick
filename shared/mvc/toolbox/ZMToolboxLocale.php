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
 * Locale methods.
 *
 * @author DerManoMann
 * @package zenmagick.store.shared.mvc.tools
 */
class ZMToolboxLocale extends ZMToolboxTool {

    /**
     * Format and display a date using the locales date/short.
     *
     * @param string date The date.
     * @param string format Optional format string to override the format provided by the active <code>ZMLocale</code>; default is <code>null</code>.
     * @return string The formatted date.
     */
    public function shortDate($date, $format=null) {
        if ($date instanceof DateTime) {
            $format = null != $format ? $format : ZMLocales::instance()->getLocale()->getFormat('date', 'short');
            return $date->format($format);
        }

        return $date;
    }

    /**
     * Format and display a date using the locales date/long.
     *
     * @param string date The date.
     * @param string format Optional format string to override the format provided by the active <code>ZMLocale</code>; default is <code>null</code>.
     * @return string The formatted date.
     */
    public function longDate($date, $format=null) {
        if ($date instanceof DateTime) {
            $format = null != $format ? $format : ZMLocales::instance()->getLocale()->getFormat('date', 'long');
            return $date->format($format);
        }

        return $date;
    }

}
