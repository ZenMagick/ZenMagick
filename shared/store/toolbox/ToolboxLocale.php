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
namespace zenmagick\apps\store\toolbox;

use zenmagick\http\toolbox\ToolboxTool;

/**
 * Locale methods.
 *
 * @author DerManoMann
 */
class ToolboxLocale extends ToolboxTool {

    /**
     * Format and display a date using the locales date/short.
     *
     * @param string date The date.
     * @param string format Optional format string to override the format provided by the active <code>Locale</code>; default is <code>null</code>.
     * @return string The formatted date.
     */
    public function shortDate($date, $format=null) {
        return \ZMLocaleUtils::dateShort($date, $format);
    }

    /**
     * Format and display a date using the locales date/long.
     *
     * @param string date The date.
     * @param string format Optional format string to override the format provided by the active <code>Locale</code>; default is <code>null</code>.
     * @return string The formatted date.
     */
    public function longDate($date, $format=null) {
        return \ZMLocaleUtils::dateLong($date, $format);
    }

    /**
     * Convenience method to lookup a locale format.
     *
     * @param string group The group.
     * @param string type Optional type.
     * @return string The format or <code>null</code>
     * @see Locale::getFormat(string,string)
     */
    public function getFormat($group, $type=null) {
        return \ZMLocaleUtils::getFormat($group, $type);
    }

}
