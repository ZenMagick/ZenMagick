<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2012 zenmagick.org
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

use zenmagick\base\Runtime;
use zenmagick\apps\store\bundles\ZenCartBundle\mock\ZenCartCheckoutOrder;

/**
 * (System) Tools.
 *
 * @author DerManoMann
 * @package zenmagick.store.shared.utils
 */
class ZMTools {
    const RANDOM_DIGITS = 'digits';
    const RANDOM_CHARS = 'chars';
    const RANDOM_MIXED = 'mixed';
    const RANDOM_HEX = 'hex';

    private static $seedDone = false;
    private static $fileOwner = null;
    private static $fileGroup = null;

    // keep track of mock
    private static $mock = false;


    /**
     * Convert a numeric range definition into an array of single values.
     *
     * <p>A range might be a single value, a range; for example <em>3-8</em> or a list of both.</p>
     * <p>Valid examples of ranges are:</p>
     * <ul>
     *  <li>3</li>
     *  <li>3,4,8</li>
     *  <li>3,4-6,8</li>
     *  <li>1,3-5,9,13,100-302</li>
     * </ul>
     *
     * @param string range The range value.
     * @return array List of numeric (int) values.
     */
    public static function parseRange($range) {
        $arr = array();
        foreach (explode(',', $range) as $token) {
            if (!empty($token)) {
                $elems = explode('-', $token);
                $size = count($elems);
                if (1 == $size && !empty($elems[0])) {
                    $elem = (int)$elems[0];
                    $arr[$elem] = $elem;
                } else if (2 == $size && !empty($elems[0]) && !empty($elems[1])) {
                    for ($ii=(int)$elems[0]; $ii<=(int)$elems[1]; ++$ii) {
                        $arr[$ii] = $ii;
                    }
                }
            }
        }
        return $arr;
    }

    /**
     * fmod variant that can handle values < 1.
     */
    public static function fmod_round($x, $y) {
        $x = strval($x);
        $y = strval($y);
        $zc_round = ($x*1000)/($y*1000);
        $zc_round_ceil = (int)($zc_round);
        $multiplier = $zc_round_ceil * $y;
        $results = abs(round($x - $multiplier, 6));
        return $results;
    }

}
