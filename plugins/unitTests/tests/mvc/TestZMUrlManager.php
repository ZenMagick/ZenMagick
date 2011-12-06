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
 * Test url manager.
 *
 * @package org.zenmagick.plugins.unitTests.tests
 * @author DerManoMann <mano@zenmagick.org>
 */
class TestZMUrlManager extends ZMTestCase {

    /**
     * Test global.
     */
    public function testGlobal() {
        $manager = new ZMUrlManager();
        $manager->setMapping(null, array('error' => array('template' => 'error', 'layout' => 'foo')));
        $mapping = $manager->findMapping('foo', 'error');
        $this->assertEqual(array('controller'=>null,'formId'=>null,'form'=>null,'view'=>null,'template'=>'error', 'layout' => 'foo'), $mapping);

        // test store mapping
        $mapping = ZMUrlManager::instance()->findMapping('foo', 'cart_not_ready');
        $this->assertEqual(array('controller'=>null,'formId'=>null,'form'=>null,'view'=>'ZMRedirectView#requestId=shopping_cart','template'=>'', 'layout' => null), $mapping);

        // test store mapping
        $mapping = ZMUrlManager::instance()->findMapping(null, 'low_stock');
        $this->assertEqual(array('controller'=>null,'formId'=>null,'form'=>null,'view'=>'ZMRedirectView#requestId=shopping_cart','template'=>'', 'layout' => null), $mapping);
    }

    /**
     * Test global default.
     */
    public function testGlobalDefault() {
        $mapping = ZMUrlManager::instance()->findMapping('checkout_shipping', 'check_cart');
        $this->assertEqual(array('controller'=>null,'formId'=>null,'form'=>null,'view'=>'ZMRedirectView#requestId=shopping_cart','template'=>'', 'layout' => null), $mapping);
    }

    /**
     * Test checkout_guest:success.
     */
    public function testCheckoutGuestSuccess() {
        $manager = ZMUrlManager::instance();
        $mapping = $manager->findMapping('checkout_guest', 'success');
        $this->assertEqual(array('controller'=>null,'formId'=>null,'form'=>null,'view'=>'ZMRedirectView#requestId=checkout_shipping','template'=>'login', 'layout' => null), $mapping);
    }

    /**
     * Test get alias.
     */
    public function testGetAlias() {
        $manager = new ZMUrlManager();
        $manager->setMappings(array('alias' => array(
            'foo' => array('requestId' => 'bar'),
            'xx' => array('parameter' => 'x=1')
        )));
        $this->assertEqual(array('requestId' => 'bar', 'parameter' => ''), $manager->getAlias('foo'));
        $this->assertEqual(array('requestId' => 'xx', 'parameter' => 'x=1'), $manager->getAlias('xx'));
    }

}
