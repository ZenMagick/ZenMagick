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
namespace zenmagick\plugins\masterPassword\controller;

use ZMPluginAdminController;

/**
 * Admin controller.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class MasterPasswordAdminController extends ZMPluginAdminController {

    /**
     * Create new instance.
     */
    public function __construct() {
        parent::__construct('masterPassword');
    }


    /**
     * {@inheritDoc}
     */
    public function processPost($request) {
        $masterPassword = $request->request->get('masterPassword', null);

        // encrypt only if not empty to allow to reset to blank
        if (!empty($masterPassword)) {
            $masterPassword = $this->container->get('authenticationManager')->getDefaultProvider()->encryptPassword($masterPassword);
        }
        // update
        $this->getPlugin()->set('masterPassword', $masterPassword);
        $this->messageService->success('Master password updated'.(empty($masterPassword)?' [reset]':'').'.');

        return $this->findView('success');
    }

}
