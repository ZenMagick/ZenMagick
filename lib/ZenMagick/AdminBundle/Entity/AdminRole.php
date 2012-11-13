<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2012 zenmagick.org
 *
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

use ZenMagick\AdminBundle\Entity\AdminUser;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\Role\RoleInterface;

/**
 * @ORM\Table(name="admin_roles")
 * @ORM\Entity
 */
class AdminRole implements RoleInterface
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="admin_role_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string $name
     *
     * @ORM\Column(name="name", type="string", length=32, unique=true, nullable=false)
     */
    private $name;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="AdminUser", mappedBy="roles")
     */
    private $admin;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->admin = new \Doctrine\Common\Collections\ArrayCollection();
    }


    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return AdminRole
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set Role
     *
     * @param string $role
     * @return AdminRole
     */
    public function setRole($role)
    {
        $this->name = $role;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getRole()
    {
        return $this->name;
    }

    /**
     * Add admin
     *
     * @param \ZenMagick\AdminBundle\Entity\AdminUser $admin
     * @return AdminRole
     */
    public function addAdmin(AdminUser $admin)
    {
        $this->admin[] = $admin;

        return $this;
    }

    /**
     * Remove admin
     *
     * @param \ZenMagick\AdminBundle\Entity\AdminUser $admin
     */
    public function removeAdmin(AdminUser $admin)
    {
        $this->admin->removeElement($admin);
    }

    /**
     * Get admin
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAdmin()
    {
        return $this->admin;
    }
}
