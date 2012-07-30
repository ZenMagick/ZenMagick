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
namespace zenmagick\apps\admin\controller;

use zenmagick\base\Beans;
use zenmagick\base\Toolbox;

/**
 * Request controller for editing (other) admin user details.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class EditAdminUserController extends \ZMController {

    /**
     * {@inheritDoc}
     */
    public function getFormData($request, $formDef=null, $formId=null) {
        $adminUser = parent::getFormData($request, $formDef, $formId);
        if (!$this->isFormSubmit($request)) {
            if (0 < ($adminUserId = $request->query->get('adminUserId'))) {
                // pre-populate with data
                $user = $this->container->get('adminUserService')->getUserForId($adminUserId);
                if (null != $user) {
                    $adminUser->setAdminUserId($user->getId());
                    $adminUser->setName($user->getName());
                    $adminUser->setEmail($user->getEmail());
                    $adminUser->setLive($user->isLive());
                    $adminUser->setRoles($user->getRoles());
                }
            }
        }
        return $adminUser;
    }

    /**
     * {@inheritDoc}
     */
    public function getViewData($request) {
        return array('roles' => $this->container->get('adminUserRoleService')->getAllRoles());
    }

    /**
     * {@inheritDoc}
     */
    public function processPost($request) {
        if ($this->handleDemo()) {
            return $this->findView('success');
        }

        $adminUserService = $this->container->get('adminUserService');

        if (null != ($editUserId = $request->request->get('adminUserId'))) {
            $adminUserForm = $this->getFormData($request);

            $user = Beans::getBean('zenmagick\\apps\\admin\\entities\\AdminUser');
            $user->setId($adminUserForm->getAdminUserId());
            $user->setName($adminUserForm->getName());
            $user->setEmail($adminUserForm->getEmail());
            $user->setRoles($adminUserForm->getRoles());
            $user->setLive(Toolbox::asBoolean($adminUserForm->getLive()));
            $clearPassword = $adminUserForm->getPassword();
            $current = $adminUserService->getUserForId($user->getId());
            if (empty($clearPassword) && null != $current) {
                // keep
                $encrypedPassword = $current->getPassword();
            } else {
                $encrypedPassword = $this->container->get('authenticationManager')->encryptPassword($clearPassword);
            }
            $user->setPassword($encrypedPassword);
            if (0 < $user->getId()) {
                $adminUserService->updateUser($user);
                $this->messageService->success(_zm('Details updated.'));
            } else {
                $adminUserService->createUser($user);
                $this->messageService->success(_zm('User created.'));
            }
        } else if (null != ($deleteUserId = $request->request->get('deleteUserId'))) {
            $adminUserService->deleteUserForId($deleteUserId);
            $this->messageService->success(_zm('User deleted.'));
        }

        return $this->findView('success');
    }

}
