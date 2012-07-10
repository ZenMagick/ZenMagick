<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2012 zenmagick.org
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
namespace zenmagick\base\locales\handler;

use zenmagick\base\Runtime;
use zenmagick\base\logging\Logging;
use zenmagick\base\locales\Locale;
use zenmagick\base\locales\handler\pomo\Translations;
use zenmagick\base\locales\handler\pomo\MO;

/**
 * Locale using <em>pomo</em>.
 *
 * <p>The domain and .mo filename (without the trailing .mo) are <strong>not</strong> synonymous. This allows to
 * load (and merge) multiple files for a single domain and locale.</p>
 *
 * @author DerManoMann <mano@zenmagick.org> <mano@zenmagick.org>
 */
class PomoLocale extends Locale {
    const DEFAULT_MO_NAME = 'messages';

    // loaded translations per domain and for the current locale
    private $translations_;


    /**
     * Create new instance.
     */
    public function __construct() {
        parent::__construct();
        $this->translations_ = array();
    }


    /**
     * {@inheritDoc}
     */
    public function init($locale, $path=null, $domain=null) {
        list($path, $yaml) = parent::init($locale, $path);
        $this->registerMOForLocale($path, $locale, self::DEFAULT_MO_NAME, $domain);
    }

    /**
     * {@inheritDoc}
     */
    public function addResource($resource, $locale=null, $domain=null) {
        $locale = null != $locale ? $locale : Runtime::getSettings()->get('zenmagick.base.locales.locale', 'en');
        $this->registerMOForLocale($resource, $locale, self::DEFAULT_MO_NAME, $domain);
    }

    /**
     * Get translations for the given domain.
     *
     * @param string domain The domain name.
     * @return Translations A <code>Translations</code> instance.
     */
    protected function getTranslationsForDomain($domain) {
        $domain = null != $domain ? $domain : Runtime::getApplication()->getContext();
        if (!array_key_exists($domain, $this->translations_)) {
            $this->translations_[$domain] = new Translations();
        }

        return $this->translations_[$domain];
    }

    /**
     * Register a .mo file for a specific locale.
     *
     * @param string basedir The locale base path.
     * @param string locale The locale.
     * @param string filename The actual filename without any path; default is <code>DEFAULT_MO_NAME</code>.
     * @param string domain The translation domain; default is <code>null</code>.
     * @return boolean <code>true</code> on success.
     */
    protected function registerMOForLocale($basedir, $locale, $filename=self::DEFAULT_MO_NAME, $domain=null) {
        $domain = null != $domain ? $domain : Runtime::getApplication()->getContext();
        $filename = (null == $filename ? $domain : $filename).'.mo';
        $path = realpath($basedir).'/'.$filename;
        if (!file_exists($basedir) || null == ($path = Locale::resolvePath($path, $locale))) {
            Runtime::getLogging()->log('unable to resolve locale path for locale="'.$locale.'"; basedir='.$basedir, Logging::TRACE);
            return;
        }
        $this->registerMO($path, $domain);
    }

    /**
     * Register a .mo file.
     *
     * @param string filename The .mo filename.
     * @param string domain The translation domain; default is <code>null</code>.
     * @return boolean <code>true</code> on success.
     */
    public function registerMO($filename, $domain=null) {
        $domain = null != $domain ? $domain : Runtime::getApplication()->getContext();
        Runtime::getLogging()->debug(sprintf('registering MO: %s for domain: %s', $filename, $domain));
        $mo = new MO();
        if (!$mo->import_from_file($filename)) {
            Runtime::getLogging()->warn(sprintf('import from MO: %s failed!', $filename));
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
    public function translate($text, $context=null, $domain=null) {
        $translations = $this->getTranslationsForDomain($domain);
        return $translations->translate($text, $context);
    }

    /**
     * {@inheritDoc}
     */
    public function translatePlural($single, $number, $plural=null, $context=null, $domain=null) {
        $plural = null == $plural ? $single : $plural;
        $translations = $this->getTranslationsForDomain($domain);
        return $translations->translate_plural($single, $plural, $number, $context);
    }

}
