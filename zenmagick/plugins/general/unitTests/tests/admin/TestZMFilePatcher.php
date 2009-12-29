<?php

/**
 * Test file patcher.
 *
 * @package org.zenmagick.plugins.unitTests.tests
 * @author DerManoMann
 * @version $Id$
 */
class TestZMFilePatcher extends ZMTestCase {

    /**
     * Clean up.
     */
    public function tearDown() {
        parent::tearDown();
        @unlink(dirname(__FILE__).'/data/file-test-insert-before-out.txt');
        @unlink(dirname(__FILE__).'/data/file-test-insert-before-edge-out.txt');
        @unlink(dirname(__FILE__).'/data/file-test-insert-after-out.txt');
        @unlink(dirname(__FILE__).'/data/file-test-insert-after-edge-out.txt');
    }

    /**
     * Test insert before.
     */
    public function testInsertBefore() {
        $file = dirname(__FILE__).'/data/file-test-insert-before.txt';
        $target = dirname(__FILE__).'/data/file-test-insert-before-out.txt';
        $patch = array(
            array(
                'match'=>'some blubb', 
                'action'=>'insert-before', 
                'data'=>'some doh'
            )
        );
        $expected = dirname(__FILE__).'/data/file-test-insert-before-expected.txt';

        $patcher = ZMLoader::make('FilePatcher', $file, $patch, $target);
        $this->assertTrue($patcher->isOpen());
        $this->assertTrue($patcher->patch());
        $this->assertEqual(@file_get_contents($expected), @file_get_contents($target));

        // undo
        $patcher = ZMLoader::make('FilePatcher', $target, $patch);
        $this->assertFalse($patcher->isOpen());
        $this->assertTrue($patcher->undo());
        $this->assertEqual(@file_get_contents($file), @file_get_contents($target));
    }

    /**
     * Test insert before edge case.
     */
    public function testInsertBeforeEdge() {
        $file = dirname(__FILE__).'/data/file-test-insert-before.txt';
        $target = dirname(__FILE__).'/data/file-test-insert-before-edge-out.txt';
        $patch = array(
            array(
                'match'=>'some bla', 
                'action'=>'insert-before', 
                'data'=>'some doh'
            )
        );
        $expected = dirname(__FILE__).'/data/file-test-insert-before-edge-expected.txt';

        $patcher = ZMLoader::make('FilePatcher', $file, $patch, $target);
        $this->assertTrue($patcher->isOpen());
        $this->assertTrue($patcher->patch());
        $this->assertEqual(@file_get_contents($expected), @file_get_contents($target));

        // undo
        $patcher = ZMLoader::make('FilePatcher', $target, $patch);
        $this->assertFalse($patcher->isOpen());
        $this->assertTrue($patcher->undo());
        $this->assertEqual(@file_get_contents($file), @file_get_contents($target));
    }

    /**
     * Test insert after.
     */
    public function testInsertAfter() {
        $file = dirname(__FILE__).'/data/file-test-insert-after.txt';
        $target = dirname(__FILE__).'/data/file-test-insert-after-out.txt';
        $patch = array(
            array(
                'match'=>'some blubb', 
                'action'=>'insert-after', 
                'data'=>'some doh'
            )
        );
        $expected = dirname(__FILE__).'/data/file-test-insert-after-expected.txt';

        $patcher = ZMLoader::make('FilePatcher', $file, $patch, $target);
        $this->assertTrue($patcher->isOpen());
        $this->assertTrue($patcher->patch());
        $this->assertEqual(@file_get_contents($expected), @file_get_contents($target));

        // undo
        $patcher = ZMLoader::make('FilePatcher', $target, $patch);
        $this->assertFalse($patcher->isOpen());
        $this->assertTrue($patcher->undo());
        $this->assertEqual(@file_get_contents($file), @file_get_contents($target));
    }

    /**
     * Test insert after edge case.
     */
    public function testInsertAfterEdge() {
        $file = dirname(__FILE__).'/data/file-test-insert-after.txt';
        $target = dirname(__FILE__).'/data/file-test-insert-after-edge-out.txt';
        $patch = array(
            array(
                'match'=>'and one more line!', 
                'action'=>'insert-after', 
                'data'=>'some doh'
            )
        );
        $expected = dirname(__FILE__).'/data/file-test-insert-after-edge-expected.txt';

        $patcher = ZMLoader::make('FilePatcher', $file, $patch, $target);
        $this->assertTrue($patcher->isOpen());
        $this->assertTrue($patcher->patch());
        $this->assertEqual(@file_get_contents($expected), @file_get_contents($target));

        // undo
        $patcher = ZMLoader::make('FilePatcher', $target, $patch);
        $this->assertFalse($patcher->isOpen());
        $this->assertTrue($patcher->undo());
        $this->assertEqual(@file_get_contents($file), @file_get_contents($target));
    }

}

?>
