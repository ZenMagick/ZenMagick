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


/**
 * Ajax user prefs controller.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class AjaxUserPrefsController extends \ZMRpcController {

    /**
     * Set pref.
     */
    public function setPref($rpcRequest) {
        $data = $rpcRequest->getData();
        $adminId = $data->adminId;
        $name = $data->name;
        $value = $data->value;

        $this->container->get('adminUserPrefService')->setPrefForName($adminId, $name, $value);
        $rpcResponse = $rpcRequest->createResponse();
        $rpcResponse->setStatus(true);

        return $rpcResponse;
    }

    /**
     * Get pref.
     */
    public function getPref($rpcRequest) {
        $data = $rpcRequest->getData();
        $adminId = $data->adminId;
        $name = $data->name;

        $value = $this->container->get('adminUserPrefService')->getPrefForName($adminId, $name);

        $rpcResponse = $rpcRequest->createResponse();
        $rpcResponse->setData(array('value' => $value));
        $rpcResponse->setStatus(true);

        return $rpcResponse;
    }

}
