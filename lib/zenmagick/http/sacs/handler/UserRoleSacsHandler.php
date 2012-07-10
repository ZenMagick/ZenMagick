<?php
/*
 * ZenMagick - Another PHP framework.
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
namespace zenmagick\http\sacs\handler;

use zenmagick\http\sacs\SacsHandler;
use zenmagick\http\sacs\handler\UserRoleCredentials;

/**
 * SACS handler that supports user and role based authorization.
 *
 * <p>Authorization is deny/allow, so unless explicitely allowed, all authorization is denied.</p>
 *
 * <p>The <em>*</em> user can be set to grant access to <strong>all</strong> users.</p>
 * <p>The <em>*</em> role can be set to grant access to <strong>authenticated</strong> users.</p>
 *
 * <p>Credentials are expected to implement the <code>zenmagick\http\sacs\handler\UserRoleCredentials</code> interface.</p>
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class UserRoleSacsHandler implements SacsHandler {

    /**
     * {@inheritDoc}
     */
    public function getName() {
        return get_class();
    }

    /**
     * {@inheritDoc}
     */
    public function evaluate($requestId, $credentials, $manager) {
        // these always apply for users/roles
        $defaultMapping = $manager->getDefaultMapping();
        $qualifiedUsers = $manager->getMappingValue($requestId, 'users', array());
        if (array_key_exists('users', $defaultMapping)) {
            $qualifiedUsers = array_merge($defaultMapping['users'], $qualifiedUsers);
        }
        $qualifiedRoles = $manager->getMappingValue($requestId, 'roles', array());
        if (array_key_exists('roles', $defaultMapping)) {
            $qualifiedRoles = array_merge($defaultMapping['roles'], $qualifiedRoles);
        }
        // special case for '*' user
        if (in_array('*', $qualifiedUsers)) {
            return true;
        }

        if (null == $credentials || !($credentials instanceof UserRoleCredentials)) {
            // need proper credentials in order to continue
            return null;
        }

        // special case for '*' role
        if (in_array('*', $qualifiedRoles)) {
            return true;
        }

        // evaluate users
        foreach ($qualifiedUsers as $user) {
            if ($user == $credentials->getName()) {
                return true;
            }
        }

        // evaluate roles
        foreach ($qualifiedRoles as $role) {
            if ($credentials->hasRole($role)) {
                return true;
            }
        }

        // nope
        return false;
    }

}
