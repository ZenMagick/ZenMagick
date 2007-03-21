<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006 radebatz.net
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
 * Languages.
 *
 * @author mano
 * @package net.radebatz.zenmagick.dao
 * @version $Id$
 */
class ZMLanguages extends ZMDao {
    var $languages_;


    /**
     * Default c'tor.
     */
    function ZMLanguages() {
        parent::__construct();

        zm_resolve_zc_class('language');
        $zcLanguage = new language();
        foreach ($zcLanguage->catalog_languages as $zccLanguage) {
            $language = $this->_newLanguage($zccLanguage);
            $this->languages_[$language->getCode()] = $language;
        }
    }

    /**
     * Default c'tor.
     */
    function __construct() {
        $this->ZMLanguages();
    }

    /**
     * Default d'tor.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Get all languages.
     *
     * @return array List of <code>ZMLanguage</code> instances.
     */
    function getLanguages() { return $this->languages_; }

    /**
     * Get language for the given code.
     *
     * @return ZMLanguage A language or <code>null</code>.
     */
    function getLanguageForCode($code) { return array_key_exists($code, $this->languages_) ? $this->languages_[$code] : null; }


    /**
     * Create new language instance.
     */
    function _newLanguage($fields) {
        $language =& $this->create("Language");
        $language->id_ = $fields['id'];
        $language->name_ = $fields['name'];
        $language->image_ = $fields['image'];
        $language->code_ = $fields['code'];
        $language->directory_ = $fields['directory'];
        return $language;
    }

}

?>
