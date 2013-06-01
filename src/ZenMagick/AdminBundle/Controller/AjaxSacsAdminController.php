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

use ZenMagick\ZenMagickBundle\Controller\RpcController;

/**
 * Ajax SACS admin controller.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class AjaxSacsAdminController extends RpcController
{
    /**
     * Add role.
     */
    public function addRole($rpcRequest)
    {
        $roleName = $rpcRequest->getData()->roleName;

        $rpcResponse = $rpcRequest->createResponse();

        try {
            $newId = $this->container->get('adminUserRoleService')->addRole($roleName);
        } catch (Exception $e) {
            $newId = null;
        }

        $translator = $this->get('translator');
        if (null != $newId) {
            $rpcResponse->setStatus(true);
            $rpcResponse->addMessage($translator->trans('Role added'), 'success');
        } else {
            $rpcResponse->setStatus(false);
            $rpcResponse->addMessage($translator->trans('Could not add role %role%', array('%role%' => $roleName)), 'error');
        }

        return $rpcResponse;
    }

    /**
     * Remove roles.
     */
    public function removeRoles($rpcRequest)
    {
        $removeRoles = $rpcRequest->getData()->roles;

        $rpcResponse = $rpcRequest->createResponse();

        $translator = $this->get('translator');
        $failed = array();
        // figure out difference
        $currentRoles = $this->container->get('adminUserRoleService')->getAllRoles();
        foreach ($currentRoles as $role) {
            if (in_array($role, $removeRoles)) {
                if ('admin' == $role) {
                    // can't have that!
                    // TODO: message
                    continue;
                }
                if (!$this->container->get('adminUserRoleService')->deleteRole($role)) {
                    $failed[] = $role;
                }
            }
        }

        if (0 == count($failed)) {
            $rpcResponse->setStatus(true);
            $rpcResponse->addMessage($translator->trans('Roles removed'), 'success');
        } else {
            $rpcResponse->setStatus(false);
            $message = $translator->trans('Could not remove role(s) %roles%', array('%roles%' => implode(', ', $failed)));
            $rpcResponse->addMessage($message, 'error');
        }

        return $rpcResponse;
    }

}
