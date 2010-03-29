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
 * Support for dual pricing mod..
 *
 * @package org.zenmagick.plugins.zm_dual_pricing
 * @author DerManoMann
 * @version $Id$
 */
class zm_dual_pricing extends Plugin {

    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct('Dual Pricing', 'Support for zen-cart dual pricing mod', '${plugin.version}');
        $this->setLoaderPolicy(ZMPlugin::LP_ALL);

        // the new prices and customer flag
        $customFields = array(
            'customers' => 'customers_whole;string',
            'products' => 'products_price_w;string',
            'products_attributes' => 'options_values_price_w;string'
        );
        foreach ($customFields as $table => $fields) {
            ZMSettings::append('zenmagick.core.database.sql.'.$table.'.customFields', $fields);
        }
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }

}
