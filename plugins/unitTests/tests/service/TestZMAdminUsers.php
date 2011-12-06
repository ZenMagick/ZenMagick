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

/**
 * Test <code>ZMAdminUsers</code>.
 *
 * @package org.zenmagick.plugins.unitTests.tests
 * @author DerManoMann <mano@zenmagick.org>
 */
class TestZMAdminUsers extends ZMTestCase {

    /**
     * Test change roles.
     */
    public function testChangeRoles() {
        ZMAdminUserRoles::instance()->addRole('helpdesk');
        $user = ZMAdminUsers::instance()->getUserForId(1);
        if ($this->assertNotNull($user)) {
            $user->addRole('helpdesk');
            ZMAdminUsers::instance()->updateUser($user);
            $user = ZMAdminUsers::instance()->getUserForId(1);
            $this->assertEqual(array('admin', 'helpdesk'), $user->getRoles());
            $user->setRoles(array('admin'));
            ZMAdminUsers::instance()->updateUser($user);
            $user = ZMAdminUsers::instance()->getUserForId(1);
            $this->assertEqual(array('admin'), $user->getRoles());
        }
        ZMAdminUserRoles::instance()->deleteRole('helpdesk');
    }

}
