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
 * Simple expectation class to compare arrays.
 *
 * @package org.zenmagick.plugins.unitTests
 * @author DerManoMann
 * @version $Id: ZMArrayEqualExpectation.php 2130 2009-04-01 23:02:16Z dermanomann $
 */
class ZMArrayEqualExpectation extends EqualExpectation {

    /**
     * {@inheritDoc}
     */
    function __construct($value, $message='%s') {
        parent::__construct($value, $message);
    }

    /**
     * {@inheritDoc}
     */
    function test($compare) {
        $value = $this->_getValue();
        if (!is_array($compare) || !is_array($value)) {
            return false;
        }
        if (count($compare) != count($value)) {
            return false;
        }

        foreach ($compare as $ii => $cvalue) {
            if (!array_key_exists($ii, $value) || $value[$ii] != $cvalue) {
                return false;
            }
        }

        return true;
    }

}

?>
