<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006 ZenMagick
 *
 * Portions Copyright (c) 2003 The zen-cart developers
 * Portions Copyright (c) 2003 osCommerce
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
 *
 * $Id$
 */
?>
<?php

    function zm_l10n($text) {
        // get the remaining args
        $args = func_get_args();
        array_shift($args);
        echo zm_l10n_lookup($text, $args);
    }

    function zm_l10n_get($text) {
        // get the remaining args
        $args = func_get_args();
        array_shift($args);
        return zm_l10n_lookup($text, $args);
    }

    function zm_l10n_lookup($text, $args = null) {
    global $zm_request;
        // get the right language
        $l10n = array();
        $lang = $zm_request->getLanguageName();
        if (array_key_exists($lang, $GLOBALS['zm_l10n_text'])) {
            $l10n = $GLOBALS['zm_l10n_text'][$lang];
        }

        // get localized text or default to provided text
        $format = isset($l10n[$text]) ? $l10n[$text] : $text;
        !isset($l10n[$text]) && zm_log("can't resolve l10n: '".$text."'", 3);

        return vsprintf($format, $args);
    }

?>
