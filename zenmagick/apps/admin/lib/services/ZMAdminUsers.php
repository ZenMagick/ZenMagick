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
 * Admin user service.
 *
 * @author DerManoMann
 * @package org.zenmagick.store.admin.services
 * @version $Id$
 */
class ZMAdminUsers extends ZMObject {

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
        return ZMObject::singleton('AdminUsers');
    }


    /**
     * Add a few things.
     *
     * @param ZMAdminUser user The user to finalize.
     * @return ZMAdminUser Finalized user.
     */
    protected function finalizeUser($user) {
        // database 1 == NOT demo...
        $user->setDemo(!$user->isDemo());

        //TODO: populate roles

        return $user;
    }

    /**
     * Get user for the given id.
     *
     * @param int id The user id.
     * @return ZMAdminUser A <code>ZMAdminUser</code> instance or <code>null</code>.
     */
    public function getUserForId($id) {
        $sql = "SELECT *
                FROM " . TABLE_ADMIN . "
                WHERE admin_id = :id";
        $args = array('id' => $id);
        return $this->finalizeUser(ZMRuntime::getDatabase()->querySingle($sql, $args, TABLE_ADMIN, 'AdminUser'));
    }

    /**
     * Get user for the given user name.
     *
     * @param string name The user name.
     * @return ZMAdminUser A <code>ZMAdminUser</code> instance or <code>null</code>.
     */
    public function getUserForName($name) {
        $sql = "SELECT *
                FROM " . TABLE_ADMIN . "
                WHERE admin_name = :name";
        $args = array('name' => $name);
        return $this->finalizeUser(ZMRuntime::getDatabase()->querySingle($sql, $args, TABLE_ADMIN, 'AdminUser'));
    }

    /**
     * Get user for the given email.
     *
     * @param string email The user email.
     * @return ZMAdminUser A <code>ZMAdminUser</code> instance or <code>null</code>.
     */
    public function getUserForEmail($email) {
        $sql = "SELECT *
                FROM " . TABLE_ADMIN . "
                WHERE admin_email = :email";
        $args = array('email' => $email);
        return $this->finalizeUser(ZMRuntime::getDatabase()->querySingle($sql, $args, TABLE_ADMIN, 'AdminUser'));
    }

}
