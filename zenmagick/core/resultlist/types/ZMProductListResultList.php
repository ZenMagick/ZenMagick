<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006,2007 ZenMagick
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
 * A result list To display products based on category, manufacturer or other
 * product properties.
 *
 * <p>This class is mainly here to allow to extend/replace with custom implementations.</p>
 *
 * @author mano
 * @package org.zenmagick.resultlist.types
 * @version $Id$
 */
class ZMProductListResultList extends ZMResultList {

    /**
     * Create new result list.
     *
     * @param array The results.
     * @param int pagination Number of results per page (default is 10)
     * @param int page The current page number (default is 0)
     */
    function __construct($results, $pagination=10, $page=0) {
        parent::__construct($results, $pagination, $page);
    }

    /**
     * Create new result list.
     *
     * @param array The results.
     * @param int pagination Number of results per page (default is 10)
     * @param int page The current page number (default is 0)
     */
    function ZMProductListResultList($results, $pagination=10, $page=0) {
        $this->__construct($results, $pagination, $page);
    }

    /**
     * Default d'tor.
     */
    function __destruct() {
        parent::__destruct();
    }

}

?>
