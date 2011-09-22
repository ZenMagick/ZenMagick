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
 * Translate the given text.
 *
 * @param string text The text to translate.
 * @param mixed context Optional translation context; default is <code>null</code>.
 * @param string domain The translation domain; default is <code>ZMLocale::DEFAULT_DOMAIN</code>.
 * @return string The translated text or, if no translation found, the original text.
 * @package org.zenmagick.core.services.locale
 */
function _zm($text, $context=null, $domain=ZMLocale::DEFAULT_DOMAIN) {
    return Runtime::getContainer()->get('localeService')->getLocale()->translate($text, $context, $domain);
}


/**
 * Translate the given text with plural option.
 *
 * @param string single The text to translate for single case.
 * @param int number The number.
 * @param string plural The text to translate for plural case; default is <code>null</code> to default to the single case.
 * @param mixed context Optional translation context; default is <code>null</code>.
 * @param string domain The translation domain; default is <code>ZMLocale::DEFAULT_DOMAIN</code>.
 * @return string The translated text or, if no translation found, the original text.
 * @package org.zenmagick.core.services.locale
 */
function _zmn($single, $number, $plural=null, $context=null, $domain=ZMLocale::DEFAULT_DOMAIN) {
    return Runtime::getContainer()->get('localeService')->getLocale()->translatePlural($single, $number, $plural, $context, $domain);
}

/**
 * Convenience version of <code>_zm</code> using a <code>null</code> context and the default domain.
 *
 * <p><strong>This method will <code>echo</code> the localized text rather than return it.</strong></p>
 *
 * @param string text The text.
 * @param mixed ... Variable number of arguments to be used as arguments for
 *  <code>vsprintf(..)</code> to insert variables into the localized text.
 * @package org.zenmagick.core.services.locale
 */
function _vzm($text) {
    // get the remaining args
    $args = func_get_args();
    array_shift($args);
    // get translation using default context/domain
    $translated = Runtime::getContainer()->get('localeService')->getLocale()->translate($text, null, ZMLocale::DEFAULT_DOMAIN);
    echo null != $args ? vsprintf($translated, $args) : $translated;
}

/**
 * Helper function to parse translated strings for block replacements.
 *
 * <p>Acts like <code>sprintf</code> but also supports block replacements.</p>
 *
 * <p>Block replacements are useful, for example, if a single word in a string should be a link. Rather than
 * splitting up the string into individual translatable strings it allows to keep the whole string as single
 * translatable unit.</p>
 *
 * <p>Example:<br>
 * String to translate: <em>Click &lt;strong>here&lt;/strong> to open a new window.</em>.<br>
 * The same with special block markers: <em>Click &lt;strong>%bhere%%&lt;/strong> to open a new window.</em>.</p>
 *
 * <p>A block marker starts with <em>%b</em> or <em>%nb</em>, with <em>n</em> being a positonal integer; example: <em>%2b</em>. The
 * block content end is marked by a double '%': <em>%%</em>.</p>
 *
 * <p>Now, to update the word <em>here</em> from the example with a link (and link text being <em>here</em>), this code
 * can be used:</p>
 *
 * <p><code>_zmsprintf(_zm('Click &lt;strong>%bhere%%&lt;/strong> to open a new window.'), '&lt;a href="">%%block%%&lt;/a>');</code></p>
 *
 * <p>The main point of this function is that the actual link text (<em>here</em>) is part of the full sentence to translate rather than
 * a single word that gets concatenated. This helps to translate the link text in the context of the sentence rather than as a single word.</p>
 *
 * <p><strong>NOTE: This function in itself does not translate at all. As seen in the above example, <code>_zm()</code> needs to be used to
 * pull the translatation first and use <code>_zmsprintf()</code> on that translated string.</p>
 *
 * @param string format The format string.
 * @param mixed mixed Variable numer parameter.
 * @return string The formatted string.
 * @package org.zenmagick.core.services.locale
 */
function _zmsprintf($format, $mixed) {
    $args = func_get_args();
    array_shift($args);

    // start with format as output
    $string = $format;

    // check for blocks
    preg_match_all('|[^%]%([0-9]*)b(.*[^%])%%|U', $string, $matches, PREG_SET_ORDER);
    if (0 < count($matches)) {
        // found blocks
        foreach ($matches as $match) {
            // default empty position parameter to 0
            $match[1] = empty($match[1]) ? 0 : (int)$match[1];
            $match[0] = trim($match[0]);
            if (isset($args[$match[1]])) {
                // parameter with that index exists
                $param = str_replace('%%block%%', $match[2], $args[$match[1]]);
                $string = str_replace($match[0], $param, $string);
            }
        }
    }

    // do normal sprintf last
    return vsprintf($string, $args);
}
