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

use zenmagick\http\sacs\SacsManager;

/**
 * Admin controller to edit role permissions.
 *
 * @author DerManoMann
 * @package zenmagick.store.admin.mvc.controller
 */
class ZMEditRoleController extends ZMController {

    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * {@inheritDoc}
     */
    public function getViewData($request) {
        $role = $request->getParameter('role');
        $permissions = array();
        // TODO: use db permissions only, not the merged manager mappings
        foreach (SacsManager::instance()->getMappings() as $requestId => $info) {
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
        return $this->findView();
    }

}
