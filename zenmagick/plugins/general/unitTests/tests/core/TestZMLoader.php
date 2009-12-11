<?php

/**
 * Test the loader.
 *
 * @package org.zenmagick.plugins.unitTests.tests
 * @author DerManoMann
 * @version $Id$
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

?>
