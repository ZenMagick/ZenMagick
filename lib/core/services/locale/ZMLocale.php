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


/**
 * Locale resolver.
 *
 * @author DerManoMann <mano@zenmagick.org> <mano@zenmagick.org>
 * @package org.zenmagick.core.services.locale
 */
interface ZMLocale {
    const DEFAULT_DOMAIN = 'defaults';


    /**
     * Get the active locale code.
     *
     * @return string The current locale code.
     */
    public function getCode();

    /**
     * Get the locale name.
     *
     * @return string The name.
     */
    public function getName();

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
    public function init($locale, $path=null);

    /**
     * Translate the given text.
     *
     * @param string text The text to translate.
     * @param mixed context Optional translation context; default is <code>null</code>.
     * @param string domain The translation domain; default is <code>ZMLocale::DEFAULT_DOMAIN</code>.
     * @return string The translated text.
     */
    public function translate($text, $context=null, $domain=ZMLocale::DEFAULT_DOMAIN);

    /**
     * Translate the given text with plural option.
     *
     * @param string single The text to translate for single case.
     * @param int number The number.
     * @param string plural The text to translate for plural case; default is <code>null</code> to default to the single case.
     * @param mixed context Optional translation context; default is <code>null</code>.
     * @param string domain The translation domain; default is <code>ZMLocale::DEFAULT_DOMAIN</code>.
     * @return string The translated text or, if no translation found, the original text.
     */
    public function translatePlural($single, $number, $plural=null, $context=null, $domain=ZMLocale::DEFAULT_DOMAIN);

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
    public function getFormat($group, $type=null);

    /**
     * Set formats.
     *
     * <p>Merge additional formats into this locale.</p>
     *
     * @param array formats Nested map of format definitions.
     */
    public function setFormats($formats);

}
