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
?>
<?php
namespace zenmagick\http\sacs\handler;


/**
 * User/role credentials.
 *
 * <p>Interface to be implemented by user classes when using the <code>zenmagick\http\sacs\handler\UserRoleSacsHandler</code> for authorization.</p>
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
interface UserRoleCredentials {

    /**
     * Get the user name.
     */
    public function getName();

    /**
     * Check if the the user has a given role.
     *
     * @param string role A role name.
     * @return boolean <code>true</code> if, and only if, the user has the given role.
     */
    public function hasRole($role);

}
