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
 * Locale service.
 *
 * <p>Delegates translations to an instance of <code>ZMLocale</code>.</p>
 *
 * <p>The implementation used can be configured via the setting 'zenmagick.core.locale.provider'. If none is
 *  configured, a default echo implementation will be used.</p>
 *
 * @author DerManoMann
 * @package org.zenmagick.core.services.locale
 */
class ZMLocales extends ZMObject implements ZMLocale {
    private $locale_;


    /**
     * Create new instance.
     */
    public function __construct() {
        parent::__construct();
        $this->locale_ = null;
    }

    /**
     * Destruct instance.
     */
    public function __destruct() {
        parent::__destruct();
    }

    /**
     * Get instance.
     */
    public static function instance() {
        return ZMObject::singleton('Locales');
    }


    /**
     * Get the locale to be used.
     *
     * @return ZMLocale The locale.
     */
    public function getLocale() {
        if (null == $this->locale_) {
            $this->locale_ = ZMBeanUtils::getBean(ZMSettings::get('zenmagick.core.locale.provider', 'EchoLocale'));
        }

        return $this->locale_;
    }


    /**
     * {@inheritDoc}
     */
    public function translate($text, $context=null, $domain=ZMLocale::DEFAULT_DOMAIN) {
        return $this->getLocale()->translate($text, $context, $domain);
    }

    /**
     * {@inheritDoc}
     */
    public function translatePlural($single, $number, $plural=null, $context=null, $domain=ZMLocale::DEFAULT_DOMAIN) {
        return $this->getLocale()->translatePlural($single, $number, $plural, $context, $domain);
    }

}
