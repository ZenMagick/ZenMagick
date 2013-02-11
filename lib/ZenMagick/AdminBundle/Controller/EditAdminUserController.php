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
namespace ZenMagick\AdminBundle\Controller;

use ZenMagick\Base\Beans;
use ZenMagick\ZenMagickBundle\Controller\DefaultController;

/**
 * Request controller for editing (other) admin user details.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class EditAdminUserController extends DefaultController
{
    /**
     * {@inheritDoc}
     */
    public function getFormData($request, $formDef=null, $formId=null)
    {
        $adminUser = parent::getFormData($request, $formDef, $formId);
        if (!$this->isFormSubmit($request)) {
            if (0 < ($adminUserId = $request->query->get('adminUserId'))) {
                // pre-populate with data
                $user = $this->container->get('adminUserService')->getUserForId($adminUserId);
                if (null != $user) {
                    $adminUser->setAdminUserId($user->getId());
                    $adminUser->setUsername($user->getUsername());
                    $adminUser->setEmail($user->getEmail());
                    $adminUser->setRoles($user->getRoles());
                }
            }
        }

        return $adminUser;
    }

    /**
     * {@inheritDoc}
     */
    public function getViewData($request)
    {
        return array('roles' => $this->container->get('adminUserRoleService')->getAllRoles());
    }

    /**
     * {@inheritDoc}
     */
    public function processPost($request)
    {
        if ($this->handleDemo()) {
            return $this->findView('success');
        }

        $adminUserService = $this->container->get('adminUserService');

        if (null != ($editUserId = $request->request->get('adminUserId'))) {
            $adminUserForm = $this->getFormData($request);

            $user = Beans::getBean('ZenMagick\\AdminBundle\\Entity\\AdminUser');
            $user->setId($adminUserForm->getAdminUserId());
            $user->setUsername($adminUserForm->getUsername());
            $user->setEmail($adminUserForm->getEmail());
            $user->setRoles($adminUserForm->getRoles());
            $clearPassword = $adminUserForm->getPassword();
            $current = $adminUserService->getUserForId($user->getId());
            if (empty($clearPassword) && null != $current) {
                // keep
                $encodedPassword = $current->getPassword();
            } else {
                $encoder = $this->get('security.encoder_factory')->getEncoder($user);
                $encodedPassword = $encoder->encodePassword($clearPassword);
            }
            $user->setPassword($encodedPassword);
            if (0 < $user->getId()) {
                $adminUserService->updateUser($user);
                $this->get('session.flash_bag')->success(_zm('Details updated.'));
            } else {
                $adminUserService->createUser($user);
                $this->get('session.flash_bag')->success(_zm('User created.'));
            }
        } elseif (null != ($deleteUserId = $request->request->get('deleteUserId'))) {
            $adminUserService->deleteUserForId($deleteUserId);
            $this->get('session.flash_bag')->success(_zm('User deleted.'));
        }

        return $this->findView('success');
    }

}
