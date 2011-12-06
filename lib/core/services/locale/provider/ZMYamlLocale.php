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
 * Locale driven by a single yaml file per language.
 *
 * <p>Expects the same format <em>locale.yaml</em> as <code>ZMPomoLocale</code>. In addition, translations are expected to
 * be under the key <em>messages</em> in the same <em>locale.yaml</em> file.</p>
 *
 * @author DerManoMann <mano@zenmagick.org> <mano@zenmagick.org>
 * @package org.zenmagick.core.services.locale.provider
 */
class ZMYamlLocale extends ZMAbstractLocale {
    private $translations_;


    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
        $this->translations_ = array();
    }

    /**
     * Destroy instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Add translations.
     *
     * @param array translations Map of translations.
     */
    public function addTanslations($translations) {
        $this->translations_ = array_merge($this->translations_, $translations);
    }

    /**
     * {@inheritDoc}
     */
    public function init($locale, $path=null) {
        list($path, $yaml) = parent::init($locale, $path);
        if (is_array($yaml) && array_key_exists('messages', $yaml)) {
            $this->addTanslations($yaml['messages']);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function translate($text, $context=null, $domain=ZMLocale::DEFAULT_DOMAIN) {
        if (array_key_exists($text, $this->translations_)) {
            return $this->translations_[$text];
        }

        return $text;
    }

    /**
     * {@inheritDoc}
     */
    public function translatePlural($single, $number, $plural=null, $context=null, $domain=ZMLocale::DEFAULT_DOMAIN) {
        // not really supported
        return $this->translate($single);
    }

}
