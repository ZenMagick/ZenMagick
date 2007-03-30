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
 *
 * $Id$
 */
?>
<?php

    // validate date
    function zm_checkdate($date, $format="DD/MM/YYYY") {
        // day
        $day = 1;
        $dpos = strpos($format, "DD");
        if (!(false === $dpos)) {
            $day = substr($date, $dpos, 2);
        }

        //month
        $month = 1;
        $mpos = strpos($format, "MM");
        if (!(false === $mpos)) {
            $month = substr($date, $mpos, 2);
        }

        // year
        $cc = 1; $yy = 1;
        $cypos = strpos($format, 'YYYY');
        if (false !== $cypos) {
            $cc = substr($date, $cypos, 2);
            $yy = substr($date, $cypos+2, 2);
        } else {
            $ypos = strpos($format, 'YY');
            if (false !== $ypos) {
                $yy = substr($date, $ypos, 2);
            }
        }
        $year = $cc.$yy;

        return @checkdate($month, $day, $year);
    }

    // validate email
    // see: http://php.inspire.net.nz/manual/en/function.eregi.php
    function zm_valid_email($email) {
        $atom = '[-a-z0-9!#$%&\'*+/=?^_`{|}~]';
        $domain = '([a-z0-9]([-a-z0-9]*[a-z0-9]+)?)';

        $regex = '^' . $atom . '+' .              // One or more atom characters.
                 '(\.' . $atom . '+)*'.           // Followed by zero or more dot separated sets of one or more atom characters.
                 '@'.                             // Followed by an "at" character.
                 '(' . $domain . '{1,63}\.)+'.    // Followed by one or max 63 domain characters (dot separated).
                 $domain . '{2,63}'.              // Must be followed by one set consisting a period of two
                 '$';                             // or max 63 domain characters.

        return eregi($regex, $email) && zen_validate_email($email);
    }


?>
