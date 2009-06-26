<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2009 ZenMagick
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
 * Order total plugin.
 *
 * @author DerManoMann
 * @package org.zenmagick.plugins.types
 * @version $Id: ZMOrderTotalPlugin.php 2133 2009-04-02 22:36:08Z dermanomann $
 */
class ZMOrderTotalPlugin extends ZMPlugin {

    /**
     * Create new plugin.
     *
     * @param string title The title.
     * @param string description The description.
     * @param string version The version.
     */
    function __construct($title='', $description='', $version='0.0') {
        parent::__construct($title, $description, $version);
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Evaluate the given cart and return resulting order totals.
     *
     * @param ZMShoppingCart cart The current cart.
     * @return mixed Either a single <code>ZMOrderTotalDetails</code>, a list of order total details
     *  (<code>ZMOrderTotalDetails</code>) or <code>null</code>.
     */
    public function calculate($cart) {
        return null;
    }

}

?>
