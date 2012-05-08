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

use zenmagick\base\Runtime;
use zenmagick\plugins\unitTests\simpletest\TestCase;

/**
 * Test ZMTools.
 *
 * @package org.zenmagick.plugins.unitTests.tests
 * @author DerManoMann <mano@zenmagick.org>
 */
class TestZMTools extends TestCase {

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
            $this->assertEqual($test[1], ZMTools::parseRange($test[0]), '%s; '.$test[0]);
        }
    }

    /**
     * Test compareStoreUrl current.
     */
    public function testCmpStoreUrlCurrent() {
        $this->assertTrue(ZMTools::compareStoreUrl('index.php?'.Runtime::getSettings()->get('zenmagick.http.request.idName').'=tests&abc=def'));
    }

    /**
     * Test compareStoreUrl two.
     */
    public function testCmpStoreUrlTwo() {
        $idName = Runtime::getSettings()->get('zenmagick.http.request.idName');
        $this->assertTrue(ZMTools::compareStoreUrl('index.php?'.$idName.'=tests&abc=def', 'index.php?'.$idName.'=tests'));
        $this->assertFalse(ZMTools::compareStoreUrl('index.php?'.$idName.'=page&id=1', 'index.php?'.$idName.'=page'));
        $this->assertTrue(ZMTools::compareStoreUrl('index.php?'.$idName.'=static&cat=foo', 'http://localhost/index.php?'.$idName.'=static&cat=foo'));
    }

    /**
     * Test compareStoreUrl incomplete.
     */
    public function testCmpStoreUrlIncomplete() {
        $idName = Runtime::getSettings()->get('zenmagick.http.request.idName');
        $this->assertTrue(ZMTools::compareStoreUrl('index.php', 'index.php?'.$idName.'=index'));
        $this->assertTrue(ZMTools::compareStoreUrl('index.php?'.$idName.'=', 'index.php?'.$idName.'=index'));
    }

    /**
     * Test compareStoreUrl some more.
     */
    public function testCmpStoreUrlSomeMore() {
        $idName = Runtime::getSettings()->get('zenmagick.http.request.idName');
        $this->assertFalse(ZMTools::compareStoreUrl('https://localhost/zen-cart/index.php?'.$idName.'=login', ''));
        $this->assertTrue(ZMTools::compareStoreUrl('https://localhost/zen-cart/index.php?'.$idName.'=login', ''.$idName.'=login'));
        $this->assertFalse(ZMTools::compareStoreUrl('https://localhost/zen-cart/index.php?'.$idName.'=wp', ''.$idName.'=login'));
        $this->assertTrue(ZMTools::compareStoreUrl('http://localhost/zen-cart/index.php?'.$idName.'=page&id=6', 'http://localhost/zen-cart/index.php?'.$idName.'=page&amp;id=6'));
        $this->assertTrue(ZMTools::compareStoreUrl('index.php?'.$idName.'=page&id=6', 'http://localhost/zen-cart/index.php?'.$idName.'=page&amp;id=6'));
        $this->assertTrue(ZMTools::compareStoreUrl('http://localhost/zen-cart/index.php?'.$idName.'=page&id=6', 'index.php?'.$idName.'=page&amp;id=6'));
        $this->assertTrue(ZMTools::compareStoreUrl('index.php?'.$idName.'=page&id=6', 'index.php?'.$idName.'=page&amp;id=6'));
    }

}
