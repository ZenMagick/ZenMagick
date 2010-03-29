<?php
/*
 * ZenMagick - Extensions for zen-cart
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
 * ZenMagick order total module to display the total.
 *
 * @package org.zenmagick.plugins.total
 * @author DerManoMann
 * @version $Id$
 */
class ZMTotalPlugin extends Plugin implements ZMOrderTotal {

    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct('Total', 'Display total', '${plugin.version}');
        $this->setLoaderPolicy(ZMPlugin::LP_FOLDER);
        $this->setPreferredSortOrder(999);
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * {@inheritDoc}
     */
    public function calculate($request, $shoppingCart) {
        $details = ZMLoader::make('OrderTotalLineDetails');
        $details->setTitle('Total');
        $details->setAmount(0);
        $details->setDisplayValue($shoppingCart->getTotal());
        return $details;
    }

}
