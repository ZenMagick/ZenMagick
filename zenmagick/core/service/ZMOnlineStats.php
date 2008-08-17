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
 * Stats about online users.
 *
 * @author DerManoMann
 * @package org.zenmagick.service
 * @version $Id$
 */
class ZMOnlineStats extends ZMObject {

    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }

    /**
     * Get instance.
     */
    public static function instance() {
        return ZMObject::singleton('OnlineStats');
    }


    /**
     * Get stats about currently online users.
     *
     * @return array Info about guest and registered users.
     */
    public function getStats() {
        $db = ZMRuntime::getDB();
        // expire old entries
        $timeAgo = (time() - 1200);
        $sql = "DELETE FROM " . TABLE_WHOS_ONLINE . "
                WHERE time_last_click < :timeAgo";
        $sql = $db->bindVars($sql, ":timeAgo", $timeAgo, 'integer');
        $db->Execute($sql);

        $sql = "select customer_id from " . TABLE_WHOS_ONLINE;
        $results = $db->Execute($sql);
        $guests = 0;
        $members = 0;
        while (!$results->EOF) {
            if (!$results->fields['customer_id'] == 0) $members++;
            if ($results->fields['customer_id'] == 0) $guests++;
            $results->MoveNext();
        }

        return array(($guests+$members), $guests, $members);
    }

}

?>
