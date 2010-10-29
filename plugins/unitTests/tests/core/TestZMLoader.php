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
 * Test the loader.
 *
 * @package org.zenmagick.plugins.unitTests.tests
 * @author DerManoMann
 */
class TestZMLoader extends ZMTestCase {

    /**
     * Test parent loader.
     */
    public function testParentLoader() {
        $parentLoader = ZMLoader::make("Loader");
        $parentLoader->registerClass('LoaderTestClass1', $this->getTestPlugin()->getPluginDirectory().'tests/core/testclasses/LoaderTestClass1.phpx');
        $parentLoader->registerClass('LoaderTestClass2', $this->getTestPlugin()->getPluginDirectory().'tests/core/testclasses/LoaderTestClass2.phpx');

        // not available yet
        $this->assertNull(ZMLoader::resolve('LoaderTestClass1'));
        $this->assertNull(ZMLoader::resolve('LoaderTestClass2'));

        // available via the loader where the class is registered
        $this->assertEqual('LoaderTestClass1', $parentLoader->resolveClass('LoaderTestClass1'));

        // add parent loader
        ZMLoader::instance()->setParent($parentLoader);

        // now both are globally available
        $this->assertEqual('LoaderTestClass1', ZMLoader::resolve('LoaderTestClass1'));
        $this->assertEqual('LoaderTestClass2', ZMLoader::resolve('LoaderTestClass2'));
    }

    /**
     * Test resolve ZM class.
     */
    public function testResolveZM() {
        ZMLoader::instance()->registerClass('LoaderTestClass4', $this->getTestPlugin()->getPluginDirectory().'tests/core/testclasses/LoaderTestClass4.phpx');
        ZMLoader::instance()->registerClass('ZMLoaderTestClass4', $this->getTestPlugin()->getPluginDirectory().'tests/core/testclasses/ZMLoaderTestClass4.phpx');
        $this->assertEqual('ZMLoaderTestClass4', ZMLoader::resolve('ZMLoaderTestClass4'));
        $this->assertEqual('LoaderTestClass4', ZMLoader::resolve('LoaderTestClass4'));

        ZMLoader::instance()->registerClass('ZMLoaderTestClass6', $this->getTestPlugin()->getPluginDirectory().'tests/core/testclasses/ZMLoaderTestClass6.phpx');
        $this->assertEqual('ZMLoaderTestClass6', ZMLoader::resolve('ZMLoaderTestClass6'));
    }

    /**
     * Test resove custom class
     */
    public function testResolveCustom() {
        ZMLoader::instance()->registerClass('LoaderTestClass5', $this->getTestPlugin()->getPluginDirectory().'tests/core/testclasses/LoaderTestClass5.phpx');
        ZMLoader::instance()->registerClass('ZMLoaderTestClass5', $this->getTestPlugin()->getPluginDirectory().'tests/core/testclasses/ZMLoaderTestClass5.phpx');
        $this->assertEqual('LoaderTestClass5', ZMLoader::resolve('LoaderTestClass5'));
        $this->assertEqual('ZMLoaderTestClass5', ZMLoader::resolve('ZMLoaderTestClass5'));

        ZMLoader::instance()->registerClass('LoaderTestClass7', $this->getTestPlugin()->getPluginDirectory().'tests/core/testclasses/LoaderTestClass7.phpx');
        $this->assertEqual('LoaderTestClass7', ZMLoader::resolve('LoaderTestClass7'));
    }

}
