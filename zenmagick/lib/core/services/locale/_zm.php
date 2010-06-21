<?php
/*
 * ZenMagick - Another PHP framework.
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
 * Translate the given text.
 *
 * @param string text The text to translate.
 * @param mixed context Optional translation context; default is <code>null</code>.
 * @param string domain The translation domain; default is <code>ZMLocale::DEFAULT_DOMAIN</code>.
 * @return string The translated text or, if no translation found, the original text.
 * @package org.zenmagick.core.services.locale
 */
function _zm($text, $context=null, $domain=ZMLocale::DEFAULT_DOMAIN) {
    return ZMLocales::instance()->translate($text, $context, $domain);
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
    return ZMLocales::instance()->translatePlural($single, $number, $plural, $context, $domain);
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
    $translated = ZMLocales::instance()->translate($text, null, ZMLocale::DEFAULT_DOMAIN);
    echo null != $args ? vsprintf($translated, $args) : $translated;
}


/*
TODO:
translations:
Latest <a href="<?php echo $admin2->url('orders') ?>"><?php _vzm('Orders') ?></a>

printf('Last %hOrders%%', '<a href="/foo">%h%%</a>');
printf('Letzte %hBestellungen%%', '<a href="/foo">%h%%</a>');

[^%]%([0-9]*)%([^%])+%%


$s = 'Letzte %hBestellungen%%';
preg_match_all('|[^%]%([0-9]*)h(.*[^%])%%|', $s, $matches);
//preg_match_all('|[^%]%|', $s, $matches);
var_dump($matches);
*/
