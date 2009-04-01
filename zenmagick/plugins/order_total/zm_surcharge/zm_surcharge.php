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
 * ZenMagick order total module to add surcharge totals based on configurable conditions.
 *
 * @package org.zenmagick.plugins.zm_surcharge
 * @author DerManoMann
 * @version $Id$
 */
class zm_surcharge extends ZMOrderTotalPlugin {

    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct('Surcharge', 'Conditional additional charges', '${plugin.version}');
        $this->setLoaderPolicy(ZMPlugin::LP_FOLDER);
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
    public function evaluate($cart) {
        return array(
            ZMLoader::make('OrderTotalDetails', 'yoo', 3),
            ZMLoader::make('OrderTotalDetails', 'doo', 4)
        );
    }

}

?>
