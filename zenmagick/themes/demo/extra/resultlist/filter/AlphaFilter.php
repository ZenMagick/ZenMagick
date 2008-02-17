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
 * Alpha key filter for products.
 *
 * @author mano
 * @version $Id$
 */
class AlphaFilter extends ZMResultListFilter {

    /**
     * Default c'tor.
     */
    function AlphaFilter() {
        parent::__construct('afilter', zm_l10n_get('First character of Name'));
    }

    /**
     * Default c'tor.
     */
    function __construct() {
        $this->AlphaFilter();
    }

    /**
     * Default d'tor.
     */
    function __destruct() {
    }


    /**
     * Return <code>true</code> if the given object is to be excluded.
     *
     * @param mixed obj The obecjt to examine.
     * @return boolean <code>true</code> if the object is to be excluded, <code>false</code> if not.
     */
    function exclude($obj) { return !zm_starts_with(strtolower($obj->getName()), $this->filterValues_[0]); }


    /**
     * Returns a list of all available filter values.
     *
     * @return array An array of string values.
     */
    function getOptions() {
        // get all used first chars
        $keys = array();
        foreach ($this->list_->getAllResults() as $result) {
            $char = strtolower(substr($result->getName(), 0, 1));
            if (!array_key_exists($char, $keys)) {
                $keys[$char] = strtoupper($char).'...';
            }
        }

        // buld options list
        $options = array();
        foreach ($keys as $key => $name) {
            $option =& $this->create("FilterOption", $name, $key, $key == $this->filterValues_[0]);
            $options[$option->getId()] = $option;
        }

        return $options;
    }

}

?>
