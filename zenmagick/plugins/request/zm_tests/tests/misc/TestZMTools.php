<?php

/**
 * Test ZMTools.
 *
 * @package org.zenmagick.plugins.zm_tests.tests
 * @author DerManoMann
 * @version $Id$
 */
class TestZMTools extends ZMTestCase {

    /**
     * Test range parser.
     */
    public function testRangeParser() {
        $tests = array(
            array('3', array(3=>3)),
            array('3,4-7', array(3=>3,4=>4,5=>5,6=>6,7=>7)),
            array('2,4,6-7', array(2=>2,4=>4,6=>6,7=>7)),
            array('6-7', array(6=>6,7=>7)),
            array('6-7,8-10', array(6=>6,7=>7,8=>8,9=>9,10=>10)),
        );
        foreach ($tests as $test) {
            $this->assertEqual($test[1], ZMTools::parseRange($test[0]));
        }
    }

    /**
     * Test compareStoreUrl current.
     */
    public function testCmpStoreUrlCurrent() {
        $this->assertTrue(ZMTools::compareStoreUrl('index.php?main_page=tests&abc=def'));
    }

    /**
     * Test compareStoreUrl two.
     */
    public function testCmpStoreUrlTwo() {
        $this->assertTrue(ZMTools::compareStoreUrl('index.php?main_page=tests&abc=def', 'index.php?main_page=tests'));
        $this->assertFalse(ZMTools::compareStoreUrl('index.php?main_page=page&id=1', 'index.php?main_page=page'));
        $this->assertTrue(ZMTools::compareStoreUrl('index.php?main_page=static&cat=foo', 'http://localhost/index.php?main_page=static&cat=foo'));
    }

    /**
     * Test compareStoreUrl incomplete.
     */
    public function testCmpStoreUrlIncomplete() {
        $this->assertTrue(ZMTools::compareStoreUrl('index.php', 'index.php?main_page=index'));
        $this->assertTrue(ZMTools::compareStoreUrl('index.php?main_page=', 'index.php?main_page=index'));
    }

    /**
     * Test compareStoreUrl some more.
     */
    public function testCmpStoreUrlSomeMore() {
        $this->assertFalse(ZMTools::compareStoreUrl('https://localhost/zen-cart/index.php?main_page=login', ''));
        $this->assertTrue(ZMTools::compareStoreUrl('https://localhost/zen-cart/index.php?main_page=login', 'main_page=login'));
        $this->assertFalse(ZMTools::compareStoreUrl('https://localhost/zen-cart/index.php?main_page=wp', 'main_page=login'));
        $this->assertTrue(ZMTools::compareStoreUrl('http://localhost/zen-cart/index.php?main_page=page&id=6', 'http://localhost/zen-cart/index.php?main_page=page&amp;id=6'));
        $this->assertTrue(ZMTools::compareStoreUrl('index.php?main_page=page&id=6', 'http://localhost/zen-cart/index.php?main_page=page&amp;id=6'));
        $this->assertTrue(ZMTools::compareStoreUrl('http://localhost/zen-cart/index.php?main_page=page&id=6', 'index.php?main_page=page&amp;id=6'));
        $this->assertTrue(ZMTools::compareStoreUrl('index.php?main_page=page&id=6', 'index.php?main_page=page&amp;id=6'));
    }

    /**
     * Test date parsing.
     */
    public function testDateParser() {
        $goodTests = array(
            // format, value, verification values
            array('dd-mm-yyyy', '19-12-2001', array('dd'=>19, 'mm'=>12, 'cc'=>20, 'yy'=>1, 'yyyy'=>2001)),
            array('ccyy/dd/mm:hh,ss,ii', '1978/13/02:19,45,18', array('dd'=>13, 'mm'=>2, 'cc'=>19, 'yy'=>78, 'yyyy'=>1978, 'hh'=>19,'ss'=>45,'ii'=>18)),
        );
        foreach ($goodTests as $test) {
            $token = ZMTools::parseDateString($test[1], $test[0]);
            foreach ($test[2] as $key => $value) {
                $this->assertEqual($value, $token[$key]);
            }
        }
    }

    /**
     * Test date translating.
     */
    public function testDateTranslater() {
        $goodTests = array(
            // format, value, target, 'result
            array('dd-mm-yyyy', '19-12-2001', 'ccyy/mm/dd', '2001/12/19')
        );
        foreach ($goodTests as $test) {
            $result = ZMTools::translateDateString($test[1], $test[0], $test[2]);
            $this->assertEqual($test[3], $result);
        }
    }

    /**
     * Test sanitize.
     */
    public function testSanitize() {
        $this->assertEqual('abc', ZMTools::sanitize('   abc'));
        $this->assertEqual('_abc_', ZMTools::sanitize('<abc>'));
        $this->assertEqual('abc', ZMTools::sanitize('abc   '));
    }

    /**
     * Test parse RSS date.
     */
    public function testParseRSSDate() {
        $this->assertEqual('12/Jan/2009', ZMTools::parseRSSDate('Mon, 12 Jan 2009 00:16:12 +0000'));
    }

    /**
     * Test inArray.
     */
    public function testInArray() {
        $tests = array(
            array('value' => 3, 'array' => array(1, 2, 3), 'expected' => true),
            array('value' => 1, 'array' => '1, 2, 3', 'expected' => true),
            array('value' => 7, 'array' => array(1, 2, 3), 'expected' => false),
            array('value' => 8, 'array' => '1, 2, 3', 'expected' => false)
        );
        foreach ($tests as $test) {
            $this->assertEqual($test['expected'], ZMTools::inArray($test['value'], $test['array']), '%s: '.$test);
        }
    }

}

?>
