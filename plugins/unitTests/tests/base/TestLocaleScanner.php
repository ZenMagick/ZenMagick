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

use ZenMagick\base\Runtime;
use ZenMagick\base\locales\LocaleScanner;
use ZenMagick\plugins\unitTests\simpletest\TestCase;

/**
 * Test LocaleScanner.
 *
 * @package org.zenmagick.plugins.unitTests.tests
 * @author DerManoMann <mano@zenmagick.org>
 */
class TestLocaleScanner extends TestCase {
    protected static $DATA_DIR;


    /**
     * Create new instance.
     */
    public function __construct() {
        parent::__construct();
        self::$DATA_DIR = $this->getTestPlugin()->getPluginDirectory().'/tests/base/data';
    }


    /**
     * Get scanner.
     */
    protected function getScanner() {
        $scanner = new LocaleScanner();
        $scanner->setFilesystem($this->container->get('filesystem'));
        return $scanner;
    }

    /**
     * Test simple
     */
    public function testSimple() {
        $map = $this->getScanner()->buildL10nMap(self::$DATA_DIR.'/l10n-simple', '.phpx');
        if ($this->assertEqual(1, count($map))) {
            $this->assertEqual(array('Yooo' => array('msg' => 'Yooo', 'plural' => null, 'context' => null, 'filename' => 'l10n-simple\l10n-test1.phpx', 'line' => 3)), array_pop($map));
        }
    }

    /**
     * Test other
     */
    public function testOther() {
        $map = $this->getScanner()->buildL10nMap(self::$DATA_DIR.'/l10n-other', '.txt');
        if ($this->assertEqual(1, count($map))) {
            $this->assertEqual(array('+ view size chart' => array('msg' => '+ view size chart', 'plural' => null, 'context' => null, 'filename' => 'l10n-other\l10n-other.txt', 'line' => 1)), array_pop($map));
        }
    }

    /**
     * Test mixed
     */
    public function testMixed() {
        $map = $this->getScanner()->buildL10nMap(self::$DATA_DIR.'/l10n-mixed', '.phpx');
        if ($this->assertEqual(1, count($map))) {
            $expected = array(
              'Yooo' => array('msg' => 'Yooo', 'plural' => null, 'context' => null, 'filename' => 'l10n-mixed\l10n-test1.phpx', 'line' => 5),
              'Foo %s Deng' =>  array('msg' => 'Foo %s Deng', 'plural' => null, 'context' => null, 'filename' => 'l10n-mixed\l10n-test1.phpx', 'line' => 8),
              'Hey %s, nice to see you again. <br /><br />Welcome back.' =>  array('msg' => 'Hey %s, nice to see you again. <br /><br />Welcome back.', 'plural' => null, 'context' => null, 'filename' => 'l10n-mixed\l10n-test1.phpx', 'line' => 15)
            );
            $this->assertEqual($expected, array_pop($map));
        }
    }

    /**
     * Test blocks.
     */
    public function testBlocks() {
        $s = 'Last %bOrders%% %1bfoo%%';
        $result = _zmsprintf($s, '<a href="">%%block%%</a>', '%%block%%-bar');
        $this->assertEqual('Last <a href="">Orders</a> foo-bar', $result);
    }

    /**
     * Test context.
     */
    public function testContext() {
        $map = $this->getScanner()->buildL10nMap(self::$DATA_DIR.'/l10n-context', '.phpx');
        if ($this->assertTrue(1 == count($map))) {
            $expected = array(
              'Yooo' => array('msg' => 'Yooo', 'plural' => null, 'context' => null, 'filename' => 'l10n-context\l10n-testx.phpx', 'line' => 5),
              'Foo %s Deng' =>  array('msg' => 'Foo %s Deng', 'plural' => 'yup', 'context' => 'ctx', 'filename' => 'l10n-context\l10n-testx.phpx', 'line' => 6),
              'dong' => array('msg' => 'dong', 'plural' => null, 'context' => 'peng', 'filename' => 'l10n-context\l10n-testx.phpx', 'line' => 7),
              '%s puh' => array('msg' => '%s puh', 'plural' => 'yap', 'context' => null, 'filename' => 'l10n-context\l10n-testx.phpx', 'line' => 8)
            );
            $this->assertEqual($expected, array_pop($map));
        }
    }

}
