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

use zenmagick\base\classloader\ClassLoader;

/**
 * Test class loader
 *
 * @package org.zenmagick.plugins.unitTests.tests
 * @author DerManoMann <mano@zenmagick.org>
 */
class TestClassLoader extends ZMTestCase {
    protected static $LOADER_DIR;


    /**
     * Create new instance.
     */
    public function __construct() {
        parent::__construct();
        self::$LOADER_DIR = $this->getTestPlugin()->getPluginDirectory().'/tests/base/loader';
    }

    /**
     * Test overlap.
     */
    public function testOverlap() {
        $classLoader = new ClassLoader();
        $classLoader->addNamespace('over', self::$LOADER_DIR);
        $classLoader->addNamespace('overlap', self::$LOADER_DIR);

        // overlapping namespace
        $this->assertTrue($classLoader->canResolve('over\OverClass1'));
        $this->assertTrue($classLoader->canResolve('overlap\OverlapClass1'));

        // same class name
        $this->assertTrue($classLoader->canResolve('over\SameName'));
        $this->assertTrue($classLoader->canResolve('overlap\SameName'));

        $classLoader->register();
        $this->assertEqual('over\SameName', get_class(new \over\SameName()));
        $this->assertEqual('overlap\SameName', get_class(new \overlap\SameName()));
    }

}
