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
 * TestCase base class.
 *
 * @package org.zenmagick.plugins.zm_tests
 * @author DerManoMann
 * @version $Id$
 */
class ZMTestCase extends UnitTestCase {

    /**
     * {@inheritDoc}
     */
    function assertEqual($first, $second, $message = '%s') {
        if (is_array($second)) {
            return $this->assert(new ArrayEqualExpectation($first), $second, $message);
        }
        return parent::assertEqual($first, $second, $message);
    }

    /**
     * {@inheritDoc}
     */
    public function assert($expectation, $compare, $message='%s') {
        $result = parent::assert($expectation, $compare, $message);
        if (!$result) {
            $location = explode(' ', trim(str_replace(array('[', ']'), '', $this->getAssertionLine())));
            $details = array(
                'line' => array_pop($location), 
                'message' => trim(str_replace('%s', '', $message)),
                'expectation' => $expectation,
                'compare' => $compare
            );
            $this->_reporter->zmPaintFail($details);
        }
        return $result;
    }

    /**
     * Get the test plugin.
     *
     * @return ZMPlugin The plugin.
     */
    public function getTestPlugin() {
        return ZMPlugins::instance()->getPluginForId('zm_tests');
    }

}

/**
 * Simple expectation class to compare arrays.
 *
 * @package org.zenmagick.plugins.zm_tests
 * @author DerManoMann
 */
class ArrayEqualExpectation extends EqualExpectation {

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
