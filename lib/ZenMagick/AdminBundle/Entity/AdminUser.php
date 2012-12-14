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

use ZenMagick\AdminBundle\Entity\AdminRole;

use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

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
 * @ORM\Entity(repositoryClass="ZenMagick\AdminBundle\Entity\AdminUserRepository")
 */
class AdminUser implements UserInterface, \Serializable
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="admin_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;
    /**
     * @var string $username
     *
     * @ORM\Column(name="admin_name", type="string", length=32, nullable=false, unique=true)
     */
    private $username;
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

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="AdminRole", inversedBy="admin")
     * @ORM\JoinTable(name="admins_to_roles",
     *   joinColumns={
     *     @ORM\JoinColumn(name="admin_id", referencedColumnName="admin_id", onDelete="CASCADE")
     *   },
     *   inverseJoinColumns={
     *     @ORM\JoinColumn(name="admin_role_id", referencedColumnName="admin_role_id")
     *   }
     * )
     */
    private $roles;

    private $salt;

    /**
     * Create new user.
     */
    public function __construct()
    {
        $this->password = null;
        $this->salt = null;
        $this->profile = 1;
        $this->prevPass1 = '';
        $this->prevPass2 = '';
        $this->prevPass3 = '';
        $this->pwdLastChangeDate = new \DateTime();
        $this->resetToken = '';
        $this->lastModified = new \DateTime();
        $this->lastLoginDate = new \DateTime();
        $this->lastLoginIp = '';
        $this->failedLogins = 0;
        $this->lockoutExpires = 0;
        $this->lastFailedAttempt = new \DateTime(); // @todo do what?
        $this->lastFailedIp = '';

        $this->live = true;
        $this->roles = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Get the id.
     *
     * @return int $id The id.
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the id.
     *
     * @param int id The id.
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get the user name
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set the user name.
     *
     * @param string username The user name.
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Get the email address.
     *
     * @return string The email address.
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set the email address.
     *
     * @parm string email The email address.
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get the password.
     *
     * @return string The encrypted password.
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set the (encrypted) password.
     *
     * @parm string password The password.
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Check if the user is a demo user.
     *
     * @return int <code>true</code> if the user is a demo admin user.
     */
    public function isDemo()
    {
        return (boolean) defined('ADMIN_DEMO') ? ADMIN_DEMO : false;
    }

    /**
     * Get an admin user pref.
     *
     * @param string name The pref name.
     * @return string The value or <code>null</code>.
     */
    public function getPref($name)
    {
        return $this->container->get('adminUserPrefService')->getPrefForName($this->getId(), $name);
    }

    /**
     * Set an admin user pref.
     *
     * @param string name The pref name.
     * @param string value The value.
     */
    public function setPref($name, $value)
    {
        $this->container->get('adminUserPrefService')->setPrefForName($this->getId(), $name, $value);

        return $this;
    }

    /**
     * Get profile
     *
     * @return integer
     */
    public function getProfile()
    {
        return $this->profile;
    }

    /**
     * Set profile
     *
     * @param integer $profile
     */
    public function setProfile($profile)
    {
       $this->profile = $profile;

        return $this;
    }

    /**
     * Get prevPass1
     *
     * @return string
     */
    public function getPrevPass1()
    {
        return $this->prevPass1;
    }

    /**
     * Set prevPass1
     *
     * @param string $prevPass1
     */
    public function setPrevPass1($prevPass1)
    {
        $this->prevPass1 = $prevPass1;

        return $this;
    }

    /**
     * Get prevPass2
     *
     * @return string
     */
    public function getPrevPass2()
    {
        return $this->prevPass2;
    }

    /**
     * Set prevPass2
     *
     * @param string $prevPass2
     */
    public function setPrevPass2($prevPass2)
    {
        $this->prevPass2 = $prevPass2;

        return $this;
    }

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
    public function setPrevPass3($prevPass3)
    {
        $this->prevPass3 = $prevPass3;

        return $this;
    }

    /**
     * Get pwdLastChangeDate
     *
     * @return \DateTime
     */
    public function getPwdLastChangeDate()
    {
        return $this->pwdLastChangeDate;
    }

    /**
     * Set pwdLastChangeDate
     *
     * @param \DateTime $pwdLastChangeDate
     */
    public function setPwdLastChangeDate($pwdLastChangeDate)
    {
        $this->pwdLastChangeDate = $pwdLastChangeDate;

        return $this;
    }

    /**
     * Get resetToken
     *
     * @return string
     */
    public function getResetToken()
    {
        return $this->resetToken;
    }

    /**
     * Set resetToken
     *
     * @param string $resetToken
     */
    public function setResetToken($resetToken)
    {
        $this->resetToken = $resetToken;

        return $this;
    }

    /**
     * Get lastModified
     *
     * @return \DateTime
     */
    public function getLastModified()
    {
        return $this->lastModified;
    }

    /**
     * Set lastModified
     *
     * @param \DateTime $lastModified
     */
    public function setLastModified($lastModified)
    {
        $this->lastModified = $lastModified;

        return $this;
    }

    /**
     * Get lastLoginDate
     *
     * @return \DateTime
     */
    public function getLastLoginDate()
    {
        return $this->lastLoginDate;
    }

    /**
     * Set lastLoginDate
     *
     * @param \DateTime $lastLoginDate
     */
    public function setLastLoginDate($lastLoginDate)
    {
        $this->lastLoginDate = $lastLoginDate;

        return $this;
    }

    /**
     * Get lastLoginIp
     *
     * @return string
     */
    public function getLastLoginIp()
    {
        return $this->lastLoginIp;
    }

    /**
     * Set lastLoginIp
     *
     * @param string $lastLoginIp
     */
    public function setLastLoginIp($lastLoginIp)
    {
        $this->lastLoginIp = $lastLoginIp;

        return $this;
    }

    /**
     * Get failedLogins
     *
     * @return integer
     */
    public function getFailedLogins()
    {
        return $this->failedLogins;
    }

    /**
     * Set failedLogins
     *
     * @param integer $failedLogins
     */
    public function setFailedLogins($failedLogins)
    {
        $this->failedLogins = $failedLogins;

        return $this;
    }

    /**
     * Get lockoutExpires
     *
     * @return integer
     */
    public function getLockoutExpires()
    {
        return $this->lockoutExpires;}

    /**
     * Set lockoutExpires
     *
     * @param integer $lockoutExpires
     */
    public function setLockoutExpires($lockoutExpires)
    {
        $this->lockoutExpires = $lockoutExpires;

        return $this;
    }

    /**
     * Get lastFailedAttempt
     *
     * @return \DateTime
     */
    public function getLastFailedAttempt()
    {
        return $this->lastFailedAttempt;
    }

    /**
     * Set lastFailedAttempt
     *
     * @param \DateTime $lastFailedAttempt
     */
    public function setLastFailedAttempt($lastFailedAttempt)
    {
        $this->lastFailedAttempt = $lastFailedAttempt;

        return $this;
    }

    /**
     * Get lastFailedIp
     *
     * @return string
     */
    public function getLastFailedIp()
    {
        return $this->lastFailedIp;
    }

    /**
     * Set lastFailedIp
     *
     * @param string $lastFailedIp
     */
    public function setLastFailedIp($lastFailedIp)
    {
        $this->lastFailedIp = $lastFailedIp;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getSalt()
    {
        return '';
    }

    /**
     * Add role
     *
     * @param  string|\ZenMagick\AdminBundle\Entity\AdminRole $role
     * @return AdminUser
     */
    public function addRole($role)
    {
        if (is_string($role)) {
            $roleObj = new AdminRole();
            $role = $roleObj->setName($role);
        }
        $this->roles[] = $role;

        return $this;
    }

    /**
     * Set Roles
     *
     * @param array $roles array of role names or objects
     */
    public function setRoles($roles)
    {
        $this->roles = new \Doctrine\Common\Collections\ArrayCollection();
        foreach ($roles as $role) {
            $this->addRole($role);
        }

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function hasRole($role)
    {
        return in_array($role, $this->getRoles());
    }

    /**
     * Remove role
     *
     * @param \ZenMagick\AdminBundle\Entity\AdminRole $role
     */
    public function removeRole(AdminRole $role)
    {
        $this->roles->removeElement($role);

        return $this;
    }

    /**
     * Get roles
     *
     * Get a list of role names assigned to this user.
     *
     * We must return an array of strings due to #1748
     *
     * @todo return \Doctrine\Common\Collections\Collection
     * @see https://github.com/symfony/symfony/issues/1748
     * @return array
     */
    public function getRoles()
    {
        $roles = array();
        foreach ($this->roles as $role) {
            $roles[] = $role->getName();
        }

        return $roles;
    }

    /**
     * {@inheritDoc}
     */
    public function eraseCredentials()
    {
    }

    /**
     * {@inheritDoc}
     */
    public function serialize()
    {
        return serialize(array($this->id));
    }

    /**
     * {@inheritDoc}
     */
    public function unserialize($serialized)
    {
        list($this->id) = unserialize($serialized);
    }

    /**
     * Get live
     *
     * @return boolean
     */
    public function getLive()
    {
        return $this->live;
    }

}
