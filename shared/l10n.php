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
     * Lookup and echo a language specific text.
     *
     * @param string text The text.
     * @param var args A variable number of arguments that will be used as arguments for
     *  <code>vsprintf(..)</code> to insert variables into the localized text.
     * @return string A localized version based on the current language, or the original text.
     * @package zenmagick.store.shared.locale
     * @deprecated use _vzm instead
     */
    function zm_l10n($text) {
        // get the remaining args
        $args = func_get_args();
        array_shift($args);
        echo _zm_l10n_lookup($text, $text, $args);
    }

    /**
     * Lookup a language specific text.
     *
     * @param string text The text.
     * @param var args A variable number of arguments that will be used as arguments for
     *  <code>vsprintf(..)</code> to insert variables into the localized text.
     * @return string A localized version based on the current language, or the original text.
     * @package zenmagick.store.shared.locale
     * @deprecated use _zm instead
     */
    function zm_l10n_get($text) {
        // get the remaining args
        $args = func_get_args();
        array_shift($args);
        return _zm_l10n_lookup($text, $text, $args);
    }

    /**
     * The actual <code>l10n</code> workhorse.
     *
     * @param string text The text.
     * @param string default A default text in case there is no localized version of the given text.
     * @param var args A variable number of arguments that will be used as arguments for
     *  <code>vsprintf(..)</code> to insert variables into the localized text.
     * @return string A localized version based on the current language, or the original text.
     * @package zenmagick.store.shared.locale
     * @deprecated use _zm instead
     */
    function _zm_l10n_lookup($text, $default, $args=null) {
        $l10n = array();
        if (isset($GLOBALS['_zm_l10n_text'])) {
            $l10n = $GLOBALS['_zm_l10n_text'];
        }

        // get localized text or default to provided default
        //$format = isset($l10n[$text]) ? $l10n[$text] : $default;
        $format = _zm($text);
        //!isset($l10n[$text]) && ZMLogging::instance()->log("can't resolve l10n: '".$text."'", ZMLogging::DEBUG);

        if (null == $args) {
            // no need for expensive printf!
            return $format;
            // preserve % in strings that do not have anything to replace
            //$format = str_replace('%', '%%', $format);
        }

        return vsprintf($format, $args);
    }
