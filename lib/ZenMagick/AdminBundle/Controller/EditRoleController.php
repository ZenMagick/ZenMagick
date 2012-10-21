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

use ZenMagick\Base\Toolbox;
use ZenMagick\Http\Sacs\SacsManager;

/**
 * Admin controller to edit role permissions.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class EditRoleController extends \ZMController {

    /**
     * Get sacs permission infos.
     *
     * @param string role The role.
     * @return array Map of permissins.
     */
    protected function getSacsPermissionInfo($role) {
        $permissions = array();
        // TODO: use db permissions only, not the merged manager mappings
        foreach ($this->container->get('sacsManager')->getMappings() as $requestId => $info) {
            if (array_key_exists('roles', $info)) {
                if (in_array($role, $info['roles'])) {
                    $permissions[$requestId] = array('type' => 'role', 'match' => 'name', 'allowed' => true);
                } else if (in_array('*', $info['roles'])) {
                    $permissions[$requestId] = array('type' => 'role', 'match' => '*', 'allowed' => true);
                }
            } else {
                if (array_key_exists('users', $info)) {
                    if (in_array('*', $info['users'])) {
                        $permissions[$requestId] = array('type' => 'user', 'match' => '*', 'allowed' => true);
                    }
                }
            }
            if (!array_key_exists($requestId, $permissions)) {
                $permissions[$requestId] = array('type' => 'role', 'match' => 'name', 'allowed' => false);
            }
        }
        return $permissions;
    }

    /**
     * {@inheritDoc}
     */
    public function getViewData($request) {
        $role = $request->getParameter('role');
        $permissions = $this->getSacsPermissionInfo($role);
        ksort($permissions);
        return array('role' => $role, 'permissions' => $permissions);
    }

    /**
     * {@inheritDoc}
     */
    public function processGet($request) {
        return $this->findView();
    }

    /**
     * {@inheritDoc}
     */
    public function processPost($request) {
        $role = $request->request->get('role');

        // changed permissions
        $permissons = $request->request->get('perm');
        // new permissions
        $requestIds = $request->request->get('requestId', array());
        $nperms = $request->request->get('nperm', array());
        for ($ii=0; $ii<count($requestIds); ++$ii) {
            if (!empty($requestIds[$ii]) && Toolbox::asBoolean($nperms[$ii])) {
                $permissons[] = $requestIds[$ii];
            }
        }

        // figure out the overlap between the current perms and the submitted ones
        $this->container->get('sacsPermissionService')->setPermissionsForRole($role, $permissons);

        return $this->findView();
    }

}
