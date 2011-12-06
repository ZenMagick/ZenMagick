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

use zenmagick\base\Runtime;

/**
 * Locale using <em>pomo</em>.
 *
 * <p>The domain and .mo filename (without the trailing .mo) are <strong>not</strong> synonymous. This allows to
 * load (and merge) multiple files for a single domain and locale.</p>
 *
 * @author DerManoMann
 * @package org.zenmagick.core.services.locale.provider
 */
class ZMPomoLocale extends ZMAbstractLocale {
    // loaded translations per domain and for the current locale
    private $translations_;
    private static $EMPTY_ = null;


    /**
     * Create new instance.
     */
    public function __construct() {
        parent::__construct();
        $this->translations_ = array();
    }

    /**
     * Destruct instance.
     */
    public function __destruct() {
        parent::__destruct();
    }


    /**
     * {@inheritDoc}
     */
    public function init($locale, $path=null) {
        list($path, $yaml) = parent::init($locale, $path);
        $this->registerMOForLocale($path, $locale, 'messages.mo');
    }

    /**
     * Get translations for the given domain.
     *
     * @param string domain The domain name.
     * @return Translations A <code>Translations</code> instance.
     */
    protected function getTranslationsForDomain($domain) {
        if (array_key_exists($domain, $this->translations_)) {
            return $this->translations_[$domain];
        } else {
            if (null == self::$EMPTY_) {
                self::$EMPTY_ = new ZMTranslations();
            }
            return self::$EMPTY_;
        }
    }

    /**
     * Register a .mo file for a specific locale.
     *
     * @param string basedir The locale base path.
     * @param string locale The locale.
     * @param string filename The actual filename without any path; default is <code>null</code> to match the domain.
     * @param string domain The translation domain; default is <code>self::DEFAULT_DOMAIN</code>.
     * @return boolean <code>true</code> on success.
     */
    public function registerMOForLocale($basedir, $locale, $filename=null, $domain=self::DEFAULT_DOMAIN) {
        $filename = null == $filename ? $domain.'.mo' : $filename;
        $path = ZMFileUtils::mkPath($basedir, 'LC_MESSAGES', $filename);
        if (null == ($path = ZMLocaleUtils::resolvePath($path, $locale))) {
            Runtime::getLogging()->debug('unable to resolve locale path for locale = "'.$locale.'"');
            return;
        }
        $this->registerMO($path, $domain);
    }

    /**
     * Register a .mo file.
     *
     * @param string filename The .mo filename.
     * @param string domain The translation domain; default is <code>self::DEFAULT_DOMAIN</code>.
     * @return boolean <code>true</code> on success.
     */
    public function registerMO($filename, $domain=self::DEFAULT_DOMAIN) {
        $mo = new ZMMO();
        if (!$mo->import_from_file($filename)) {
            return false;
        }

        if (array_key_exists($domain, $this->translations_)) {
            $mo->merge_with($this->translations_[$domain]);
        }

        $this->translations_[$domain] = $mo;
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function translate($text, $context=null, $domain=self::DEFAULT_DOMAIN) {
        $translations = $this->getTranslationsForDomain($domain);
        return $translations->translate($text, $context);
    }

    /**
     * {@inheritDoc}
     */
    public function translatePlural($single, $number, $plural=null, $context=null, $domain=self::DEFAULT_DOMAIN) {
        $plural = null == $plural ? $single : $plural;
        $translations = $this->getTranslationsForDomain($domain);
        return $translations->translate_plural($single, $plural, $number, $context);
    }

}
