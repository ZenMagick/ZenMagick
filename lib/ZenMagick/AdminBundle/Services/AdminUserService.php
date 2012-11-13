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
namespace ZenMagick\AdminBundle\Services;

use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\ORM\EntityManager;

/**
 * Admin user service.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class AdminUserService
{
    public $roleService;

    private $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function setRoleService($roleService)
    {
        $this->roleService = $roleService;
    }

    /**
     * Get user for the given id.
     *
     * @param int id The user id.
     * @return UserInterface A <code>UserInterface</code> instance or <code>null</code>.
     */
    public function getUserForId($id)
    {
        return $this->em->find('AdminBundle:AdminUser', $id);
    }

    /**
     * Get all users.
     *
     * @param boolean demoOnly Optional flag to load demo users only; default is <code>false</code>.
     * @return array List of <code>UserInterface</code> instances.
     */
    public function getAllUsers($demoOnly = false)
    {
        $repository = $this->em->getRepository('AdminBundle:AdminUser');
        if ($demoOnly) {
            $users = $repository->findBy(array('live' => false));
        } else {
            $users = $repository->findAll();
        }

        return $users;
    }

    /**
     * Get user for the given email.
     *
     * @param string email The user email.
     * @return UserInterface A <code>UserInterface</code> instance or <code>null</code>.
     */
    public function getUserForEmail($email)
    {
        $repository = $this->em->getRepository('AdminBundle:AdminUser');
        return $repository->findOneByEmail($email);
    }

    /**
     * Create user.
     *
     * @param ZMUser user The user.
     * @return UserInterface The updated <code>UserInterface</code> instance.
     */
    public function createUser($user)
    {
        $this->em->persist($user);
        $this->em->flush();
        $this->roleService->setRolesForId($user->getId(), $user->getRoles());

        return true;
    }

    /**
     * Update user.
     *
     * @param ZMUser user The user.
     * @return UserInterface The updated <code>UserInterface</code> instance.
     */
    public function updateUser($user)
    {
        \ZMRuntime::getDatabase()->updateModel('admin', $user);
        $this->roleService->setRolesForId($user->getId(), $user->getRoles());

        return true;
    }

    /**
     * Delete user.
     *
     * @param int id The user id.
     */
    public function deleteUserForId($id)
    {
        $user = $this->getUserFor($id);
        $this->em->remove($user);
        $this->em->flush();
        return true;
    }

}
