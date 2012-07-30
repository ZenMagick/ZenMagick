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

use zenmagick\base\Runtime;

/**
 * Admin controller for admin user management.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class AdminUsersController extends \ZMController {

    /**
     * {@inheritDoc}
     */
    public function processGet($request) {
        $user = $this->getUser();
        $resultSource = new \ZMObjectResultSource('zenmagick\\apps\\admin\\entities\\AdminUser', 'adminUserService', "getAllUsers", !$user->isLive());
        $resultList = $this->container->get('ZMResultList');
        $resultList->setResultSource($resultSource);
        $resultList->setPageNumber($request->query->get('page', 1));

        $data = array('resultList' => $resultList);
        return $this->findView(null, $data);
    }

}
