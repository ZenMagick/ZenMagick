<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2010 zenmagick.org
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


/**
 * Ajax SACS admin controller.
 *
 * @author DerManoMann
 * @package zenmagick.store.admin.mvc.controller.ajax
 */
class ZMAjaxSacsAdminController extends ZMRpcController {

    /**
     * Add role.
     */
    public function addRole($rpcRequest) {
        $roleName = $rpcRequest->getData()->roleName;

        $rpcResponse = $rpcRequest->createResponse();

        try {
            $newId = ZMAdminUserRoles::instance()->addRole($roleName);
        } catch (Exception $e) {
            $newId = null;
        }

        if (null != $newId) {
            $rpcResponse->setStatus(true);
            $rpcResponse->addMessage(_zm('Role added'), 'success');
        } else {
            $rpcResponse->setStatus(false);
            $rpcResponse->addMessage(_zm('Could not add role \''.$roleName.'\''), 'error');
        }

        return $rpcResponse;
    }

    /**
     * Remove roles.
     */
    public function removeRoles($rpcRequest) {
        $removeRoles = $rpcRequest->getData()->roles;

        $rpcResponse = $rpcRequest->createResponse();

        $failed = array();
        // figure out difference
        $currentRoles = ZMAdminUserRoles::instance()->getAllRoles();
        foreach ($currentRoles as $role) {
            if (in_array($role, $removeRoles)) {
                if ('admin' == $role) {
                    // can't have that!
                    // TODO: message
                    continue;
                }
                if (!ZMAdminUserRoles::instance()->deleteRole($role)) {
                    $failed[] = $role;
                }
            }
        }

        if (0 == count($failed)) {
            $rpcResponse->setStatus(true);
            $rpcResponse->addMessage(_zm('Roles removed'), 'success');
        } else {
            $rpcResponse->setStatus(false);
            $rpcResponse->addMessage(_zm('Could not remove role(s) \''.implode(', ', $failed).'\''), 'error');
        }

        return $rpcResponse;
    }

}
