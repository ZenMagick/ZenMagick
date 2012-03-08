<?php
/*
 * ZenMagick - Smart e-commerce
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
     * Invoke a protected/private method on the given instance.
     *
     * @param mixed target The target class/instance.
     * @param string method The method name.
     * @param array args Optional method arguments; default is an emtpy array.
     * @return mixed The method result.
     */
    public function invokeMethod($target, $method, $args=array()) {
        $class = new ReflectionClass($target);
        $method = $class->getMethod($method);
        $method->setAccessible(true);
        return $method->invokeArgs($target, (array)$args);
    }

    /**
     * Validate the filename for the given class.
     *
     * @param ClassLoader classLoader The class loader to test.
     * @param string name The class name.
     */
    protected function assertFilename(ClassLoader $classLoader, $name) {
        $filename = $this->invokeMethod($classLoader, 'resolveClass', $name);
        $expected = self::$LOADER_DIR.'/'.$name.'.php';
        $this->assertEqual(realpath($expected), realpath($filename));
    }

    /**
     * Assert overlap classes for the given class loader.
     *
     * @param ClassLoader classLoader The class loader to test.
     */
    protected function assertOverlapClasses(ClassLoader $classLoader) {
        // overlapping namespace
        $this->assertTrue($classLoader->canResolve('over\OverClass1'));
        $this->assertFilename($classLoader, 'over\OverClass1');
        $this->assertTrue($classLoader->canResolve('overlap\OverlapClass1'));
        $this->assertFilename($classLoader, 'overlap\OverlapClass1');

        // same class name
        $this->assertTrue($classLoader->canResolve('over\SameName'));
        $this->assertFilename($classLoader, 'over\SameName');
        $this->assertTrue($classLoader->canResolve('overlap\SameName'));
        $this->assertFilename($classLoader, 'overlap\SameName');

        // sub
        $this->assertTrue($classLoader->canResolve('over\sub\SameName'));
        $this->assertFilename($classLoader, 'over\sub\SameName');
        $this->assertTrue($classLoader->canResolve('overlap\sub\SameName'));
        $this->assertFilename($classLoader, 'overlap\sub\SameName');
    }

    /**
     * Test overlap.
     */
    public function testOverlap() {
        $classLoader = new ClassLoader();
        $classLoader->addNamespace('over', self::$LOADER_DIR);
        $classLoader->addNamespace('overlap', self::$LOADER_DIR);
        $this->assertOverlapClasses($classLoader);
    }

    /**
     * Test overlap reverse.
     */
    public function testOverlapReverse() {
        $classLoader = new ClassLoader();
        $classLoader->addNamespace('overlap', self::$LOADER_DIR);
        $classLoader->addNamespace('over', self::$LOADER_DIR);
        $this->assertOverlapClasses($classLoader);
    }

    /**
     * Test overlap folded.
     */
    public function testOverlapFolded() {
        $classLoader = new ClassLoader();
        $classLoader->addNamespace('over', self::$LOADER_DIR.'/over@over');
        $classLoader->addNamespace('overlap', self::$LOADER_DIR.'/overlap@overlap');
        $this->assertOverlapClasses($classLoader);
    }

    /**
     * Test overlap folded reverse.
     */
    public function testOverlapFoldedReverse() {
        $classLoader = new ClassLoader();
        $classLoader->addNamespace('overlap', self::$LOADER_DIR.'/overlap@overlap');
        $classLoader->addNamespace('over', self::$LOADER_DIR.'/over@over');
        $this->assertOverlapClasses($classLoader);
    }

    /**
     * Validate the filename for the given class.
     *
     * @param ClassLoader classLoader The class loader to test.
     * @param string name The class name.
     */
    protected function assertFilename2(ClassLoader $classLoader, $name) {
        $filename = $this->invokeMethod($classLoader, 'resolveClass', $name);
        $expected = dirname(self::$LOADER_DIR).'/'.$name.'.php';
        $this->assertEqual(realpath($expected), realpath($filename));
    }

    /**
     * Assert overlap2 classes for the given class loader.
     *
     * @param ClassLoader classLoader The class loader to test.
     */
    protected function assertOverlap2Classes(ClassLoader $classLoader) {
        // overlapping namespace
        $this->assertTrue($classLoader->canResolve('loader\over\OverClass1'));
        $this->assertFilename2($classLoader, 'loader\over\OverClass1');
        $this->assertTrue($classLoader->canResolve('loader\overlap\OverlapClass1'));
        $this->assertFilename2($classLoader, 'loader\overlap\OverlapClass1');

        // same class name
        $this->assertTrue($classLoader->canResolve('loader\over\SameName'));
        $this->assertFilename2($classLoader, 'loader\over\SameName');
        $this->assertTrue($classLoader->canResolve('loader\overlap\SameName'));
        $this->assertFilename2($classLoader, 'loader\overlap\SameName');

        // sub
        $this->assertTrue($classLoader->canResolve('loader\over\sub\SameName'));
        $this->assertFilename2($classLoader, 'loader\over\sub\SameName');
        $this->assertTrue($classLoader->canResolve('loader\overlap\sub\SameName'));
        $this->assertFilename2($classLoader, 'loader\overlap\sub\SameName');
    }

    /**
     * Test overlap2 folded.
     */
    public function testOverlap2Folded() {
        $classLoader = new ClassLoader();
        $classLoader->addNamespace('loader\over', self::$LOADER_DIR.'/over@loader\over');
        $classLoader->addNamespace('loader\overlap', self::$LOADER_DIR.'/overlap@loader\overlap');
        $this->assertOverlap2Classes($classLoader);
    }

    /**
     * Test overlap2 folded reverse.
     */
    public function testOverlap2FoldedReverse() {
        $classLoader = new ClassLoader();
        $classLoader->addNamespace('loader\overlap', self::$LOADER_DIR.'/overlap@loader\overlap');
        $classLoader->addNamespace('loader\over', self::$LOADER_DIR.'/over@loader\over');
        $this->assertOverlap2Classes($classLoader);
    }

}
