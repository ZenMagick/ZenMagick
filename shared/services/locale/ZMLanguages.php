<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2010 zenmagick.org
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
 */
?>
<?php


/**
 * Languages service.
 *
 * @author DerManoMann
 * @package zenmagick.store.shared.services.locale
 */
class ZMLanguages extends ZMObject {
    private $languages;


    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
        $this->languages = null;
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }

    /**
     * Get instance.
     */
    public static function instance() {
        return ZMRuntime::singleton('Languages');
    }


    /**
     * Load languages.
     */
    function _load() {
        $sql = "SELECT *
                FROM " . TABLE_LANGUAGES . "
                ORDER BY sort_order";
        $this->languages = array();
        foreach (Runtime::getDatabase()->query($sql, array(), TABLE_LANGUAGES, 'Language') as $language) {
            $this->languages[$language->getCode()] = $language;
        }
    }

    /**
     * Get all languages.
     *
     * @return array List of <code>ZMLanguage</code> instances.
     */
    function getLanguages() {
        if (null === $this->languages) {
            $this->_load();
        } 

        return $this->languages;
    }

    /**
     * Get language for the given code.
     *
     * @param string code The language code.
     * @return ZMLanguage A language or <code>null</code>.
     */
    function getLanguageForCode($code) {
        if (null === $this->languages) {
            $this->_load();
        }

        return isset($this->languages[$code]) ? $this->languages[$code] : null; 
    }

    /**
     * Get language for the given id.
     *
     * @param int id The language id.
     * @return ZMLanguage A language or <code>null</code>.
     */
    function getLanguageForId($id) {
        if (null === $this->languages) {
            $this->_load();
        }

        foreach ($this->languages as $language) {
            if ($language->getId() == $id) {
                return $language;
            }
        }

        return null;
    }

}
