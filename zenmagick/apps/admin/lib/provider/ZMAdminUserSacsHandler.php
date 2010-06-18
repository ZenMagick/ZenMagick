<?php
/*
 * ZenMagick - Extensions for zen-cart
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
        $qualifiedRoles = $manager->getMappingValue($requestId, 'roles');
        if (null === $qualifiedRoles) {
            // we handle all requests!
            return false;
        }

        if (empty($qualifiedRoles) || 0 == count($qualifiedRoles)) {
            // no role required; ie. login
            return true;
        }

        if (null == $credentials || (null != $credentials && !($credentials instanceof ZMAdminUser))) {
            return false;
        }

        // check for role match
        foreach ($qualifiedRoles as $role) {
            if ($credentials->hasRole($role)) {
                return true;
            }
        }

        return false;
    }

}
