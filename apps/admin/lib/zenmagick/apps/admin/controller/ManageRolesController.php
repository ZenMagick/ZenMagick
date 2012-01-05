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
namespace zenmagick\apps\admin\controller;

use zenmagick\http\sacs\SacsManager;

/**
 * Admin controller to manage roles.
 *
 * @author DerManoMann <mano@zenmagick.org>
 * @package zenmagick.apps.admin.controller
 */
class ManageRolesController extends \ZMController {

    /**
     * {@inheritDoc}
     */
    public function getViewData($request) {
        $roles = $this->container->get('adminUserRoleService')->getAllRoles();
        $sacsManager = $this->container->get('sacsManager');
        $mappings = $sacsManager->getMappings();
        $defaultMapping = $sacsManager->getDefaultMapping();
        return array('roles' => $roles, 'mappings' => $mappings, 'defaultMapping' => $defaultMapping);
    }

    /**
     * {@inheritDoc}
     */
    public function processPost($request) {
        $adminUserRoleService = $this->container->get('adminUserRoleService');
        if (null == ($newRole = $request->getParameter('newRole'))) {
            // check for changes
            $updatedRoles = $request->getParameter('roles');
            $currentRoles = $adminUserRoleService->getAllRoles();
            foreach ($updatedRoles as $role) {
                if (!in_array($role, $currentRoles)) {
                    if (null == ($newId = $adminUserRoleService->addRole($role))) {
                        $this->messageService->error('Adding role failed');
                    }
                }
            }
            foreach ($currentRoles as $role) {
                if (!in_array($role, $updatedRoles)) {
                    if ('admin' == $role) {
                        continue;
                    }
                    if (null == ($newId = $adminUserRoleService->deleteRole($role))) {
                        $this->messageService->error('Deleting role failed');
                    }
                }
            }
        } else {
            if (null == ($newId = $adminUserRoleService->addRole($newRole))) {
                $this->messageService->error('Adding role failed');
            }
        }

        return $this->findView();
    }

}
