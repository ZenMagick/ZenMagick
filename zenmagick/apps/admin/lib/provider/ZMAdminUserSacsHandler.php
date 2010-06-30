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
 * Handle access control and security mappings.
 *
 * <p>Access control mappings define the level of authentication required for resources.
 * Resources in this context are controller or page requests.</p>
 *
 * <p>Controller/resources marked as secure will be enforcer by redirects using SSL (if configured), if
 * non secure HTTP is used to access them.</p>
 *
 * @author DerManoMann
 * @package org.zenmagick.admin.mvc
 */
class ZMAdminUserSacsHandler extends ZMObject implements ZMSacsHandler {


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
    public function getName() {
        return get_class();
    }


    /**
     * {@inheritDoc}
     */
    public function evaluate($requestId, $credentials, $manager) {
        $qualifiedUsers = $manager->getMappingValue($requestId, 'users');
        $qualifiedRoles = $manager->getMappingValue($requestId, 'roles');

        if (null === $qualifiedUsers && null === $qualifiedRoles) {
            // neither users nor roles are set at all
            return true;
        }

        if (null == $credentials || (null != $credentials && !($credentials instanceof ZMAdminUser))) {
            // need proper user in order to continue
            return false;
        }

        if (true == $this->evaluateUsers($credentials, $qualifiedUsers)) {
            return true;
        }

        if (true == $this->evaluateRoles($credentials, $qualifiedRoles)) {
            return true;
        }

        // nothing left to evaluate
        return false;
    }

    /**
     * Evaluate user based permission.
     *
     * @param mixed credentials The user credentials.
     * @param array users List of authorized users.
     */
    public function evaluateUsers($credentials, $users) {
        if (null === $users) {
            return false;
        }

        // check for user match
        foreach ($users as $user) {
            if ($user == $credentials->getName()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Evaluate role based permission.
     *
     * @param mixed credentials The user credentials.
     * @param array roles List of authorized roles.
     */
    public function evaluateRoles($credentials, $roles) {
        if (null === $roles) {
            return false;
        }

        // if set and no element, $roles will be a string ' '
        if (empty($roles)  || 0 == count($roles)) {
            // allow all authenticated users
            return true;
        }

        // check for role match
        foreach ($roles as $role) {
            if ($credentials->hasRole($role)) {
                return true;
            }
        }

        return false;
    }

}
