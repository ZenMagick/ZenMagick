<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2010 zenmagick.org
 *
 * Portions Copyright (c) 2003 The zen-cart developers
 * Portions Copyright (c) 2003 osCommerce
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
 * Request controller for editing (other) admin user details.
 *
 * @author DerManoMann
 * @package org.zenmagick.store.mvc.controller
 * @version $Id$
 */
class ZMEditAdminUserController extends ZMController {

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
    public function getFormData($request) {
        $adminUser = parent::getFormData($request);
        if (!$this->isFormSubmit($request)) {
            if (0 < ($adminUserId = $request->getParameter('adminUserId'))) {
                // prepopulate with data
                $user = ZMAdminUsers::instance()->getUserForId($adminUserId);
                if (null != $user) {
                    $adminUser->setAdminUserId($user->getId());
                    $adminUser->setName($user->getName());
                    $adminUser->setEmail($user->getEmail());
                    $adminUser->setDemo($user->isDemo());
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
        return array('roles' => ZMAdminUserRoles::instance()->getAllRoles());
    }

    /**
     * {@inheritDoc}
     */
    public function processPost($request) {
        if (null != ($editUserId = $request->getParameter('adminUserId'))) {
            $adminUserForm = $this->getFormData($request);

            $user = ZMLoader::make('AdminUser');
            $user->setId($adminUserForm->getAdminUserId());
            $user->setName($adminUserForm->getName());
            $user->setEmail($adminUserForm->getEmail());
            $user->setRoles($adminUserForm->getRoles());
            // sigh - bad logic
            $user->setDemo(!ZMLangUtils::asBoolean($adminUserForm->getDemo()));
            $clearPassword = $adminUserForm->getPassword();
            $current = ZMAdminUsers::instance()->getUserForId($user->getId());
            if (empty($clearPassword) && null != $current) {
                // keep
                $encrypedPassword = $current->getPassword();
            } else {
                $encrypedPassword = ZMAuthenticationManager::instance()->encryptPassword($clearPassword);
            }
            $user->setPassword($encrypedPassword);
            if (0 < $user->getId()) {
                ZMAdminUsers::instance()->updateUser($user);
                ZMMessages::instance()->success(zm_l10n_get('Details updated.'));
            } else {
                ZMAdminUsers::instance()->createUser($user);
                ZMMessages::instance()->success(zm_l10n_get('User created.'));
            }
        } else if (null != ($deleteUserId = $request->getParameter('deleteUserId'))) {
            ZMAdminUsers::instance()->deleteUserForId($deleteUserId);
            ZMMessages::instance()->success(zm_l10n_get('User deleted.'));
        }

        return $this->findView('success');
    }

}
