<?php

/**
 * Test url comparison.
 *
 * @package org.zenmagick.plugins.zm_tests.tests
 * @author DerManoMann
 * @version $Id$
 */
class TestCompareUrls extends ZMTestCase {

    /**
     * Test current.
     */
    public function testCurrent() {
        $this->assertTrue(ZMTools::compareStoreUrl('index.php?main_page=tests&abc=def'));
    }

    /**
     * Test two.
     */
    public function testTwo() {
        $this->assertTrue(ZMTools::compareStoreUrl('index.php?main_page=tests&abc=def', 'index.php?main_page=tests'));
        $this->assertFalse(ZMTools::compareStoreUrl('index.php?main_page=page&id=1', 'index.php?main_page=page'));
        $this->assertTrue(ZMTools::compareStoreUrl('index.php?main_page=static&cat=foo', 'http://localhost/index.php?main_page=static&cat=foo'));
    }

    /**
     * Test incomplete.
     */
    public function testIncomplete() {
        $this->assertTrue(ZMTools::compareStoreUrl('index.php', 'index.php?main_page=index'));
        $this->assertTrue(ZMTools::compareStoreUrl('index.php?main_page=', 'index.php?main_page=index'));
    }

    /**
     * Test some more.
     */
    public function testSomeMore() {
        $this->assertFalse(ZMTools::compareStoreUrl('https://localhost/zen-cart/index.php?main_page=login', ''));
        $this->assertTrue(ZMTools::compareStoreUrl('https://localhost/zen-cart/index.php?main_page=login', 'main_page=login'));
        $this->assertFalse(ZMTools::compareStoreUrl('https://localhost/zen-cart/index.php?main_page=wp', 'main_page=login'));
        $this->assertTrue(ZMTools::compareStoreUrl('http://localhost/zen-cart/index.php?main_page=page&id=6', 'http://localhost/zen-cart/index.php?main_page=page&amp;id=6'));
        $this->assertTrue(ZMTools::compareStoreUrl('index.php?main_page=page&id=6', 'http://localhost/zen-cart/index.php?main_page=page&amp;id=6'));
        $this->assertTrue(ZMTools::compareStoreUrl('http://localhost/zen-cart/index.php?main_page=page&id=6', 'index.php?main_page=page&amp;id=6'));
        $this->assertTrue(ZMTools::compareStoreUrl('index.php?main_page=page&id=6', 'index.php?main_page=page&amp;id=6'));
    }

}

?>
