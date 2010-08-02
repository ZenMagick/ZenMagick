<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2010 zenmagick.org
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
 * A admin user.
 *
 * @author DerManoMann
 * @package zenmagick.store.admin.model
 */
class ZMAdminUser extends ZMObject implements ZMUserRoleCredentials {
    private $id_;
    private $name_;
    private $email_;
    private $password_;
    private $demo_;
    private $roles_;


    /**
     * Create new user.
     */
    function __construct() {
        parent::__construct();
        $this->id_ = 0;
        $this->name_ = '';
        $this->email_ = null;
        $this->password_ = null;
        $this->demo_ = true;
        $this->roles_ = array();
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Get the id.
     *
     * @return int The id.
     */
    public function getId() { return $this->id_; }

    /**
     * Set the id.
     *
     * @param int id The id.
     */
    public function setId($id) { $this->id_ = $id; }

    /**
     * {@inheritDoc}
     */
    public function getName() { return $this->name_; }

    /**
     * Set the name.
     *
     * @param string name The name.
     */
    public function setName($name) { $this->name_ = $name; }

    /**
     * Get the email address.
     *
     * @return string The email address.
     */
    public function getEmail() { return $this->email_; }

    /**
     * Set the email address.
     *
     * @parm string email The email address.
     */
    public function setEmail($email) { $this->email_ = $email; }

    /**
     * Get the password.
     *
     * @return string The encrypted password.
     */
    public function getPassword() { return $this->password_; }

    /**
     * Set the (encrypted) password.
     *
     * @parm string password The password.
     */
    public function setPassword($password) { $this->password_ = $password; }

    /**
     * Check if the user is a demo user.
     *
     * @return boolean <code>true</code> if the user is a demo admin user.
     */
    public function isDemo() { return $this->demo_; }

    /**
     * Set the demo flag.
     *
     * @parm boolean demo The new value.
     */
    public function setDemo($demo) { $this->demo_ = $demo; }

    /**
     * Get the roles for this user.
     *
     * @return array A list of (string) role names.
     */
    public function getRoles() { return $this->roles_; }

    /**
     * Set the roles for this user.
     *
     * @param array roles A list of (string) role names.
     */
    public function setRoles($roles) { $this->roles_ = $roles; }

    /**
     * Add a role.
     *
     * @param string role The role to add.
     */
    public function addRole($role) { $this->roles_[] = $role; }

    /**
     * {@inheritDoc}
     */
    public function hasRole($role) { return in_array($role, $this->roles_); }

    /**
     * Get an admin user pref.
     *
     * @param string name The pref name.
     * @return string The value or <code>null</code>.
     */
    public function getPref($name) {
        return ZMAdminUserPrefs::instance()->getPrefForName($this->getId(), $name);
    }

    /**
     * Set an admin user pref.
     *
     * @param string name The pref name.
     * @param string value The value.
     */
    public function setPref($name, $value) {
        ZMAdminUserPrefs::instance()->setPrefForName($this->getId(), $name, $value);
    }

}
