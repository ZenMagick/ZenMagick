<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006 ZenMagick
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
 * Base DAO class.
 *
 * @author mano
 * @package net.radebatz.zenmagick
 * @version $Id$
 */
class ZMDao extends ZMObject {
    var $db_;


    /**
     * Default c'tor.
     */
    function ZMDao() {
    global $zm_runtime;

        parent::__construct();

        $this->db_ =& $zm_runtime->getDB();
    }

    /**
     * Default c'tor.
     */
    function __construct() {
        $this->ZMDao();
    }

    /**
     * Default d'tor.
     */
    function __destruct() {
        parent::__destruct();
    }

}

?>
