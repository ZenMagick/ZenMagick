<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006 ZenMagick
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


    /**
     * Get a phpBB database connection.
     *
     * @package net.radebatz.zenmagick.misc
     * @return queryFactory A <code>zen-cart</code><code>queryFactor</code> instance.
     */
    function zm_get_bb_db() {
    global $sniffer;
        $db = new queryFactory();
        $db->connect($sniffer->phpBB['dbhost'], $sniffer->phpBB['dbuser'], $sniffer->phpBB['dbpasswd'], $sniffer->phpBB['dbname'], USE_PCONNECT, false);
        return $db;
    }


    /**
     * Checks whether a given phpBB nickname exists or not.
     *
     * @package net.radebatz.zenmagick.misc
     * @param string nick The nickname to validate.
     * @return bool <code>true</code> if the nickname exists, <code>false</code> if not.
     */
    function zm_bb_nickname_exists($nick) {
    global $zm_runtime, $sniffer;
        if (!$zm_runtime->isBBActive())
            return false;

        $db = zm_get_bb_db();
        $sql = "select * from " . $sniffer->phpBB['users_table'] . " where username = '" . zm_db_prepare_input($nick) . "'";
        $results = $db->Execute($sql);

        // reconnect to store
        $db->close();
        $zm_runtime->reconnectDB();
        return $results->RecordCount() > 0;
    }


    /**
     * Createsa phpBB account.
     *
     * @package net.radebatz.zenmagick.misc
     * @param string nick The nickname.
     * @param string email The email address.
     * @param string password The password.
     * @return bool <code>true</code> if the account was created, <code>false</code> if the account creation failed.
     */
    function zm_bb_create_account($nick, $email, $password) {
    global $zm_runtime, $sniffer;
        if (!$zm_runtime->isBBActive())
            return false;

        $db = zm_get_bb_db();
        $sql = "select max(user_id) as total from " . $sniffer->phpBB['users_table'];
        $results = $db->Execute($sql);
        $nextUserId = ($results->fields['total'] + 1);

        $sql = "insert into " . $sniffer->phpBB['users_table'] . "
                (user_id, username, user_password, user_email, user_regdate)
                values
                ('" . (int)$nextUserId . "', '" . zm_db_prepare_input($nick) . "', '" . 
                 md5($password) . "', '" . zm_db_prepare_input($email) . "', '" . time() ."')";
        $db->Execute($sql);
        $sql = "INSERT INTO " . $sniffer->phpBB['groups_table'] . " (group_name, group_description, group_single_user, group_moderator)
				VALUES ('', 'Personal User', 1, 0)";
        $db->Execute($sql);
		    $groupId = $db->Insert_ID();
        $sql = "INSERT INTO " . $sniffer->phpBB['user_group_table'] . " (user_id, group_id, user_pending)
				VALUES ($nextUserId, $groupId, 0)";
        $db->Execute($sql);

        // reconnect to store
        $db->close();
        $zm_runtime->reconnectDB();

        return true;
    }

    /**
     * Createsa phpBB href.
     *
     * @package net.radebatz.zenmagick.misc
     * @return string A href to the phpBB main page.
     */
    function zm_get_phpBB_href() {
    global $phpBB;
        return zm_href($phpBB->phpBB['phpbb_url'] . FILENAME_BB_INDEX);
    }

?>
