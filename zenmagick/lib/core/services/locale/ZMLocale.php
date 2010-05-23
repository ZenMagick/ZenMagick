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
 * Locale resolver.
 *
 * @author DerManoMann
 * @package org.zenmagick.core.services.locale
 */
interface ZMLocale {
    const DEFAULT_DOMAIN = 'defaults';

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

}
