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
 * Currencies.
 *
 * @author mano
 * @package net.radebatz.zenmagick.dao
 * @version $Id$
 */
class ZMCurrencies extends ZMDao {
    var $currencies_;

    // create new instance
    function ZMCurrencies() {
    global $currencies;

        parent::__construct();

        $this->currencies_ = array();

        if (isset($currencies) && is_object($currencies)) {
            reset($currencies->currencies);
            while (list($id, $arr) = each($currencies->currencies)) {
                $this->currencies_[$id] =& $this->create("Currency", $id, $arr);
            }
        }
    }

    // create new instance
    function __construct() {
        $this->ZMCurrencies();
    }

    function __destruct() {
    }


    // getter/setter
    function getCurrencies() { return $this->currencies_; }
    function getCurrencyForId($id) { return $this->currencies_[$id]; }

}

?>
