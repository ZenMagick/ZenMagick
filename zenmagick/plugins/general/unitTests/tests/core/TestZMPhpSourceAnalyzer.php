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
 * Test PHP source analyzer.
 *
 * @package org.zenmagick.plugins.unitTests.tests
 * @author DerManoMann
 * @version $Id$
 */
class TestZMPhpSourceAnalyzer extends ZMTestCase {

    /**
     * Get an (empty) dependency map.
     *
     * @return array A dependency map.
     */
    protected function getDepsMap() {
        return array(
            'contains' => array(
                'classes' => array(), 
                'interfaces' => array()
            ), 
            'depends' => array(
                'classes' => array(), 
                'interfaces' => array()
            )
        );
    }

    /**
     * Get test source.
     *
     * @param string filename The file name.
     */
    protected function getSourceFor($filename) {
        $path = ZMFileUtils::mkPath(array($this->getTestPlugin()->getPluginDirectory(), 'tests', 'core', 'testclasses'));
        $lines = ZMFileUtils::getFileLines($path.$filename);
        return implode("\n", $lines);
    }

    /**
     * Test simple.
     */
    public function testSimple() {
        $expected = $this->getDepsMap();
        $expected['contains']['classes'][] = 'ZMBasicClass';
        $source = $this->getSourceFor('ZMPhpSourceAnalyzerTestClassSimple.phpx'); 
        $deps = ZMPhpSourceAnalyzer::getDependencies($source);
        $this->assertEqual($expected, $deps);
    }

    /**
     * Test extends.
     */
    public function testExtends() {
        $expected = $this->getDepsMap();
        $expected['contains']['classes'][] = 'ZMBasicClass';
        $expected['depends']['classes'][] = 'ZMParentClass';
        $source = $this->getSourceFor('ZMPhpSourceAnalyzerTestClassExtends.phpx'); 
        $deps = ZMPhpSourceAnalyzer::getDependencies($source);
        $this->assertEqual($expected, $deps);
    }

    /**
     * Test implements.
     */
    public function testImplements() {
        $expected = $this->getDepsMap();
        $expected['contains']['classes'][] = 'ZMBasicClass';
        $expected['depends']['interfaces'][] = 'ZMSomeInterface';
        $source = $this->getSourceFor('ZMPhpSourceAnalyzerTestClassImplements.phpx'); 
        $deps = ZMPhpSourceAnalyzer::getDependencies($source);
        $this->assertEqual($expected, $deps);
    }

    /**
     * Test mixed.
     */
    public function testMixed() {
        $expected = $this->getDepsMap();
        $expected['contains']['classes'][] = 'ZMBasicClass';
        $expected['depends']['classes'][] = 'ZMParentClass';
        $expected['depends']['interfaces'][] = 'ZMSomeInterface';
        $source = $this->getSourceFor('ZMPhpSourceAnalyzerTestClassMixed.phpx'); 
        $deps = ZMPhpSourceAnalyzer::getDependencies($source);
        $this->assertEqual($expected, $deps);
    }

    /**
     * Test multi.
     */
    public function testMulti() {
        $expected = $this->getDepsMap();
        $expected['contains']['classes'] = array('ZMBasicClass', 'ZMOtherClass');
        $expected['contains']['interfaces'] = array('Foo');
        $expected['depends']['classes'] = array('ZMParentClass');
        $expected['depends']['interfaces'] = array('ZMSomeInterface', 'Bar');
        $source = $this->getSourceFor('ZMPhpSourceAnalyzerTestClassMulti.phpx'); 
        $deps = ZMPhpSourceAnalyzer::getDependencies($source);
        $this->assertEqual($expected, $deps);
    }

    /**
     * Test multi multi.
     */
    public function testMultiMulti() {
        $expected = $this->getDepsMap();
        $expected['contains']['classes'] = array('ZMBasicClass', 'ZMOtherClass');
        $expected['contains']['interfaces'] = array('Foo');
        $expected['depends']['classes'] = array('ZMParentClass');
        $expected['depends']['interfaces'] = array('ZMSomeInterface', 'ZMSomeOtherInterface', 'Feng', 'Shui', 'Bar', 'Doh');
        $source = $this->getSourceFor('ZMPhpSourceAnalyzerTestClassMultiMulti.phpx'); 
        $deps = ZMPhpSourceAnalyzer::getDependencies($source);
        $this->assertEqual($expected, $deps);
    }

    /**
     * Test multi multi whitespace.
     */
    public function testMultiMultiWS() {
        $expected = $this->getDepsMap();
        $expected['contains']['classes'] = array('ZMBasicClass', 'ZMOtherClass');
        $expected['contains']['interfaces'] = array('Foo');
        $expected['depends']['classes'] = array('ZMParentClass');
        $expected['depends']['interfaces'] = array('ZMSomeInterface', 'ZMSomeOtherInterface', 'Feng', 'Shui', 'Bar', 'Doh');
        $source = $this->getSourceFor('ZMPhpSourceAnalyzerTestClassMultiMultiWS.phpx'); 
        $deps = ZMPhpSourceAnalyzer::getDependencies($source);
        $this->assertEqual($expected, $deps);
    }

    /**
     * Test tree builder.
     */
    public function testTreeBuilder() {
        $expected = array(
            array('ClassA.phpx', 'foo.phpx', 'InterfaceC.phpx'),
            array('ClassB.phpx', 'ClassF.phpx', 'InterfaceD.phpx', 'InterfaceE.phpx'),
            array('ClassG.phpx')
        );

        $path = ZMFileUtils::mkPath(array($this->getTestPlugin()->getPluginDirectory(), 'tests', 'core', 'testclasses', 'deps'));
        $tree = ZMPhpSourceAnalyzer::buildDepdencyTree(ZMLoader::findIncludes($path, '.phpx', true));
        // strip path to make comparable
        foreach ($tree as $ii => $files) {
            $tmp = array();
            foreach ($files as $file) {
                $tmp[] = basename($file);
            }
            $tree[$ii] = $tmp;
        }
        $this->assertEqual($expected, $tree);
    }

}
