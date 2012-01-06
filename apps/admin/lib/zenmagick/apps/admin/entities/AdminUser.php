<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2011 zenmagick.org
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
namespace zenmagick\apps\admin\entities;

use zenmagick\base\ZMObject;
use zenmagick\http\sacs\handler\UserRoleCredentials;

use Doctrine\ORM\Mapping AS ORM;

/**
 * A admin user.
 *
 * @author DerManoMann <mano@zenmagick.org>
 * @ORM\Table(name="admin")
 * @ORM\Entity
 */
class AdminUser extends ZMObject implements UserRoleCredentials {
    /**
     * @var integer $id
     *
     * @ORM\Column(name="admin_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;
    /**
     * @var string $name
     *
     * @ORM\Column(name="admin_name", type="string", length=32, nullable=false)
     */
    private $name;
    /**
     * @var string $email
     *
     * @ORM\Column(name="admin_email", type="string", length=96, nullable=false)
     */
    private $email;
    /**
     * @var string $password
     *
     * @ORM\Column(name="admin_pass", type="string", length=40, nullable=false)
     */
    private $password;
    /**
     * @var boolean $adminLevel
     *
     * @ORM\Column(name="admin_level", type="boolean", nullable=false)
     */
    private $live;

    private $roles;


    /**
     * Create new user.
     */
    public function __construct() {
        parent::__construct();
        $this->id = 0;
        $this->name = '';
        $this->email = null;
        $this->password = null;
        $this->live = false;
        $this->roles = array();
    }

    /**
     * Get the id.
     *
     * @return int $id The id.
     */
    public function getId() { return $this->id; }

    /**
     * Set the id.
     *
     * @param int id The id.
     */
    public function setId($id) { $this->id = $id; }

    /**
     * Get the name
     */
    public function getName() { return $this->name; }

    /**
     * Set the name.
     *
     * @param string name The name.
     */
    public function setName($name) { $this->name = $name; }

    /**
     * Get the email address.
     *
     * @return string The email address.
     */
    public function getEmail() { return $this->email; }

    /**
     * Set the email address.
     *
     * @parm string email The email address.
     */
    public function setEmail($email) { $this->email = $email; }

    /**
     * Get the password.
     *
     * @return string The encrypted password.
     */
    public function getPassword() { return $this->password; }

    /**
     * Set the (encrypted) password.
     *
     * @parm string password The password.
     */
    public function setPassword($password) { $this->password = $password; }

    /**
     * Check if the user is a live user.
     *
     * @return boolean <code>true</code> if the user is a live admin user.
     */
    public function isLive() { return $this->live; }

    /**
     * Set the live flag.
     *
     * @parm boolean live The new value.
     */
    public function setLive($live) { $this->live = $live; }

    /**
     * Get the roles for this user.
     *
     * @return array A list of (string) role names.
     */
    public function getRoles() { return $this->roles; }

    /**
     * Set the roles for this user.
     *
     * @param array roles A list of (string) role names.
     */
    public function setRoles($roles) { $this->roles = $roles; }

    /**
     * Add a role.
     *
     * @param string role The role to add.
     */
    public function addRole($role) { $this->roles[] = $role; }

    /**
     * {@inheritDoc}
     */
    public function hasRole($role) { return in_array($role, $this->roles); }

    /**
     * Get an admin user pref.
     *
     * @param string name The pref name.
     * @return string The value or <code>null</code>.
     */
    public function getPref($name) {
        return $this->container->get('adminUserPrefService')->getPrefForName($this->getId(), $name);
    }

    /**
     * Set an admin user pref.
     *
     * @param string name The pref name.
     * @param string value The value.
     */
    public function setPref($name, $value) {
        $this->container->get('adminUserPrefService')->setPrefForName($this->getId(), $name, $value);
    }

}
