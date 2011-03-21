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
 * Test file patcher.
 *
 * @package org.zenmagick.plugins.unitTests.tests
 * @author DerManoMann
 */
class TestZMFilePatcher extends ZMTestCase {
    private $dataPath_;


    /**
     * Create new instance.
     */
    public function __construct() {
        parent::__construct();
        $this->dataPath_ = ZMFileUtils::mkPath($this->getTestsBaseDirectory(), 'core', 'data');
    }


    /**
     * Clean up.
     */
    public function tearDown() {
        parent::tearDown();
        @unlink($this->dataPath_.'file-test-insert-before-out.txt');
        @unlink($this->dataPath_.'file-test-insert-before-edge-out.txt');
        @unlink($this->dataPath_.'file-test-insert-after-out.txt');
        @unlink($this->dataPath_.'file-test-insert-after-edge-out.txt');
    }

    /**
     * Test insert before.
     */
    public function testInsertBefore() {
        $file = $this->dataPath_.'file-test-insert-before.txt';
        $target = $this->dataPath_.'file-test-insert-before-out.txt';
        $patch = array(
            array(
                'match'=>'some blubb',
                'action'=>'insert-before',
                'data'=>'some doh'
            )
        );
        $expected = $this->dataPath_.'file-test-insert-before-expected.txt';

        $patcher = ZMLoader::make('ZMFilePatcher', $file, $patch, $target);
        $this->assertTrue($patcher->isOpen());
        $this->assertTrue($patcher->patch());
        $this->assertEqual(@file_get_contents($expected), @file_get_contents($target));

        // undo
        $patcher = ZMLoader::make('ZMFilePatcher', $target, $patch);
        $this->assertFalse($patcher->isOpen());
        $this->assertTrue($patcher->undo());
        $this->assertEqual(@file_get_contents($file), @file_get_contents($target));
    }

    /**
     * Test insert before edge case.
     */
    public function testInsertBeforeEdge() {
        $file = $this->dataPath_.'file-test-insert-before.txt';
        $target = $this->dataPath_.'file-test-insert-before-edge-out.txt';
        $patch = array(
            array(
                'match'=>'some bla',
                'action'=>'insert-before',
                'data'=>'some doh'
            )
        );
        $expected = $this->dataPath_.'file-test-insert-before-edge-expected.txt';

        $patcher = ZMLoader::make('ZMFilePatcher', $file, $patch, $target);
        $this->assertTrue($patcher->isOpen());
        $this->assertTrue($patcher->patch());
        $this->assertEqual(@file_get_contents($expected), @file_get_contents($target));

        // undo
        $patcher = ZMLoader::make('ZMFilePatcher', $target, $patch);
        $this->assertFalse($patcher->isOpen());
        $this->assertTrue($patcher->undo());
        $this->assertEqual(@file_get_contents($file), @file_get_contents($target));
    }

    /**
     * Test insert after.
     */
    public function testInsertAfter() {
        $file = $this->dataPath_.'file-test-insert-after.txt';
        $target = $this->dataPath_.'file-test-insert-after-out.txt';
        $patch = array(
            array(
                'match'=>'some blubb',
                'action'=>'insert-after',
                'data'=>'some doh'
            )
        );
        $expected = $this->dataPath_.'file-test-insert-after-expected.txt';

        $patcher = ZMLoader::make('ZMFilePatcher', $file, $patch, $target);
        $this->assertTrue($patcher->isOpen());
        $this->assertTrue($patcher->patch());
        $this->assertEqual(@file_get_contents($expected), @file_get_contents($target));

        // undo
        $patcher = ZMLoader::make('ZMFilePatcher', $target, $patch);
        $this->assertFalse($patcher->isOpen());
        $this->assertTrue($patcher->undo());
        $this->assertEqual(@file_get_contents($file), @file_get_contents($target));
    }

    /**
     * Test insert after edge case.
     */
    public function testInsertAfterEdge() {
        $file = $this->dataPath_.'file-test-insert-after.txt';
        $target = $this->dataPath_.'file-test-insert-after-edge-out.txt';
        $patch = array(
            array(
                'match'=>'and one more line!',
                'action'=>'insert-after',
                'data'=>'some doh'
            )
        );
        $expected = $this->dataPath_.'file-test-insert-after-edge-expected.txt';

        $patcher = ZMLoader::make('ZMFilePatcher', $file, $patch, $target);
        $this->assertTrue($patcher->isOpen());
        $this->assertTrue($patcher->patch());
        $this->assertEqual(@file_get_contents($expected), @file_get_contents($target));

        // undo
        $patcher = ZMLoader::make('ZMFilePatcher', $target, $patch);
        $this->assertFalse($patcher->isOpen());
        $this->assertTrue($patcher->undo());
        $this->assertEqual(@file_get_contents($file), @file_get_contents($target));
    }

}
