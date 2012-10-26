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
namespace ZenMagick\AdminBundle\Entity;

use ZenMagick\Base\ZMObject;
use ZenMagick\Http\Sacs\Handler\UserRoleCredentials;

use Doctrine\ORM\Mapping as ORM;

/**
 * An admin user.
 *
 * @todo remove admin_level from here
 * @author DerManoMann <mano@zenmagick.org>
 * @ORM\Table(name="admin",
 *  uniqueConstraints={@ORM\UniqueConstraint(name="idx_admin_name_zen",columns={"admin_name"})},
 *  indexes={
 *      @ORM\Index(name="idx_admin_name_zen", columns={"admin_name"}),
 *      @ORM\Index(name="idx_admin_email_zen", columns={"admin_email"}),
 *      @ORM\Index(name="idx_admin_profile_zen", columns={"admin_profile"}),
 *  })
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
     * @ORM\Column(name="admin_name", type="string", length=32, nullable=false, unique=true)
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
     * @var boolean $live
     *
     * @ORM\Column(name="admin_level", type="boolean", nullable=false)
     */
    private $live;
    /**
     * @var integer $profile
     *
     * @ORM\Column(name="admin_profile", type="integer", nullable=false)
     */
    private $profile;

    /**
     * @var string $prevPass1
     *
     * @ORM\Column(name="prev_pass1", type="string", length=40, nullable=false)
     */
    private $prevPass1;

    /**
     * @var string $prevPass2
     *
     * @ORM\Column(name="prev_pass2", type="string", length=40, nullable=false)
     */
    private $prevPass2;

    /**
     * @var string $prevPass3
     *
     * @ORM\Column(name="prev_pass3", type="string", length=40, nullable=false)
     */
    private $prevPass3;

    /**
     * @var \DateTime $pwdLastChangeDate
     *
     * @ORM\Column(name="pwd_last_change_date", type="datetime", nullable=false)
     */
    private $pwdLastChangeDate;

    /**
     * @var string $resetToken
     *
     * @ORM\Column(name="reset_token", type="string", length=60, nullable=false)
     */
    private $resetToken;

    /**
     * @var \DateTime $lastModified
     *
     * @ORM\Column(name="last_modified", type="datetime", nullable=false)
     */
    private $lastModified;

    /**
     * @var \DateTime $lastLoginDate
     *
     * @ORM\Column(name="last_login_date", type="datetime", nullable=false)
     */
    private $lastLoginDate;

    /**
     * @var string $lastLoginIp
     *
     * @ORM\Column(name="last_login_ip", type="string", length=15, nullable=false)
     */
    private $lastLoginIp;

    /**
     * @var integer $failedLogins
     *
     * @ORM\Column(name="failed_logins", type="smallint", nullable=false)
     */
    private $failedLogins;

    /**
     * @var integer $lockoutExpires
     *
     * @ORM\Column(name="lockout_expires", type="integer", nullable=false)
     */
    private $lockoutExpires;

    /**
     * @var \DateTime $lastFailedAttempt
     *
     * @ORM\Column(name="last_failed_attempt", type="datetime", nullable=false)
     */
    private $lastFailedAttempt;

    /**
     * @var string $lastFailedIp
     *
     * @ORM\Column(name="last_failed_ip", type="string", length=15, nullable=false)
     */
    private $lastFailedIp;

    // @todo use adminRoles!
    private $roles;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="AdminRole", inversedBy="admin")
     * @ORM\JoinTable(name="admins_to_roles",
     *   joinColumns={
     *     @ORM\JoinColumn(name="admin_id", referencedColumnName="admin_id")
     *   },
     *   inverseJoinColumns={
     *     @ORM\JoinColumn(name="admin_role_id", referencedColumnName="admin_role_id")
     *   }
     * )
     */
    private $adminRole;

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
        $this->adminRole = new \Doctrine\Common\Collections\ArrayCollection();
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

    /**
     * Get profile
     *
     * @return integer
     */
    public function getProfile() { return $this->profile; }

    /**
     * Set profile
     *
     * @param integer $profile
     */
    public function setProfile($profile) {$this->profile = $profile; }

    /**
     * Get prevPass1
     *
     * @return string
     */
    public function getPrevPass1() { return $this->prevPass1; }

    /**
     * Set prevPass1
     *
     * @param string $prevPass1
     */
    public function setPrevPass1($prevPass1) { $this->prevPass1 = $prevPass1; }

    /**
     * Get prevPass2
     *
     * @return string
     */
    public function getPrevPass2() { return $this->prevPass2; }

    /**
     * Set prevPass2
     *
     * @param string $prevPass2
     */
    public function setPrevPass2($prevPass2) { $this->prevPass2 = $prevPass2; }

    /**
     * Get prevPass3
     *
     * @return string
     */
    public function getPrevPass3()
    {
        return $this->prevPass3;
    }

    /**
     * Set prevPass3
     *
     * @param string $prevPass3
     */
    public function setPrevPass3($prevPass3) { $this->prevPass3 = $prevPass3; }

    /**
     * Get pwdLastChangeDate
     *
     * @return \DateTime
     */
    public function getPwdLastChangeDate() { return $this->pwdLastChangeDate; }

    /**
     * Set pwdLastChangeDate
     *
     * @param \DateTime $pwdLastChangeDate
     */
    public function setPwdLastChangeDate($pwdLastChangeDate) { $this->pwdLastChangeDate = $pwdLastChangeDate; }

    /**
     * Get resetToken
     *
     * @return string
     */
    public function getResetToken() { return $this->resetToken; }

    /**
     * Set resetToken
     *
     * @param string $resetToken
     */
    public function setResetToken($resetToken) { $this->resetToken = $resetToken; }

    /**
     * Get lastModified
     *
     * @return \DateTime
     */
    public function getLastModified() { return $this->lastModified; }


    /**
     * Set lastModified
     *
     * @param \DateTime $lastModified
     */
    public function setLastModified($lastModified) { $this->lastModified = $lastModified; }

    /**
     * Get lastLoginDate
     *
     * @return \DateTime
     */
    public function getLastLoginDate() { return $this->lastLoginDate; }


    /**
     * Set lastLoginDate
     *
     * @param \DateTime $lastLoginDate
     */
    public function setLastLoginDate($lastLoginDate) { $this->lastLoginDate = $lastLoginDate; }

    /**
     * Get lastLoginIp
     *
     * @return string
     */
    public function getLastLoginIp() { return $this->lastLoginIp; }


    /**
     * Set lastLoginIp
     *
     * @param string $lastLoginIp
     */
    public function setLastLoginIp($lastLoginIp) { $this->lastLoginIp = $lastLoginIp; }

    /**
     * Get failedLogins
     *
     * @return integer
     */
    public function getFailedLogins() { return $this->failedLogins; }

    /**
     * Set failedLogins
     *
     * @param integer $failedLogins
     */
    public function setFailedLogins($failedLogins) { $this->failedLogins = $failedLogins; }

    /**
     * Get lockoutExpires
     *
     * @return integer
     */
    public function getLockoutExpires() { return $this->lockoutExpires;}

    /**
     * Set lockoutExpires
     *
     * @param integer $lockoutExpires
     */
    public function setLockoutExpires($lockoutExpires) { $this->lockoutExpires = $lockoutExpires; }

    /**
     * Get lastFailedAttempt
     *
     * @return \DateTime
     */
    public function getLastFailedAttempt() { return $this->lastFailedAttempt; }

    /**
     * Set lastFailedAttempt
     *
     * @param \DateTime $lastFailedAttempt
     */
    public function setLastFailedAttempt($lastFailedAttempt){ $this->lastFailedAttempt = $lastFailedAttempt; }

    /**
     * Get lastFailedIp
     *
     * @return string
     */
    public function getLastFailedIp() { return $this->lastFailedIp; }

    /**
     * Set lastFailedIp
     *
     * @param string $lastFailedIp
     */
    public function setLastFailedIp($lastFailedIp) { $this->lastFailedIp = $lastFailedIp; }

    /**
     * Add adminRole
     *
     * @todo rename to roles once we can use it
     * @param ZenMagick\ZenCartBundle\Entity\AdminRole $adminRole
     */
    public function addAdminRole(\ZenMagick\ZenCartBundle\Entity\AdminRole $adminRole) {
        $this->adminRole[] = $adminRole;
    }

    /**
     * Remove adminRole
     *
     * @param ZenMagick\ZenCartBundle\Entity\AdminRole $adminRole
     * @todo rename to role once we can use it
     */
    public function removeAdminRole(\ZenMagick\ZenCartBundle\Entity\AdminRole $adminRole) {
        $this->adminRole->removeElement($adminRole);
    }

    /**
     * Get adminRole
     *
     * @return Doctrine\Common\Collections\Collection
     * @todo rename to role once we can use it
     */
    public function getAdminRole() { return $this->adminRole; }
}
