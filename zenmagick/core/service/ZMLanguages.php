<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2008 ZenMagick
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
 * @package org.zenmagick.service
 * @author DerManoMann
 * @version $Id$
 */
class ZMLanguages extends ZMObject {
    var $languages_;


    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
        $this->languages_ = null;
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Load languages.
     */
    function _load() {
        $db = ZMRuntime::getDB();
        $sql = "select languages_id, name, code, image, directory from " . TABLE_LANGUAGES . " order by sort_order";
        $results = $db->Execute($sql);

        $this->languages_ = array();
        while (!$results->EOF) {
            $language = $this->_newLanguage($results->fields);
            $results->MoveNext();
            $this->languages_[$language->getCode()] = $language;
        }
    }

    /**
     * Get all languages.
     *
     * @return array List of <code>ZMLanguage</code> instances.
     */
    function getLanguages() {
        if (null === $this->languages_) {
            $this->_load();
        } 

        return $this->languages_;
    }

    /**
     * Get language for the given code.
     *
     * @param string code The language code.
     * @return ZMLanguage A language or <code>null</code>.
     */
    function getLanguageForCode($code) {
        if (null === $this->languages_) {
            $this->_load();
        }

        return isset($this->languages_[$code]) ? $this->languages_[$code] : null; 
    }

    /**
     * Get language for the given id.
     *
     * @param int id The language id.
     * @return ZMLanguage A language or <code>null</code>.
     */
    function getLanguageForId($id) {
        if (null === $this->languages_) {
            $this->_load();
        }

        foreach ($this->languages_ as $language) {
            if ($language->id_ == $id) {
                return $language;
            }
        }

        return null;
    }


    /**
     * Create new language instance.
     */
    function _newLanguage($fields) {
        $language = $this->create("Language");
        $language->id_ = $fields['languages_id'];
        $language->name_ = $fields['name'];
        $language->image_ = $fields['image'];
        $language->code_ = $fields['code'];
        $language->directory_ = $fields['directory'];
        return $language;
    }

}

?>
