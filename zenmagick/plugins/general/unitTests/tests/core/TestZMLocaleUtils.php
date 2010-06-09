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
 * Test ZMLocaleUtils.
 *
 * @package org.zenmagick.plugins.unitTests.tests
 * @author DerManoMann
 * @version $Id$
 */
class TestZMLocaleUtils extends ZMTestCase {
    protected static $DATA_DIR;

    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
        self::$DATA_DIR = ZMFileUtils::mkPath(array($this->getTestPlugin()->getPluginDirectory(), 'tests', 'core', 'data'));
    }


    /**
     * Test simple
     */
    public function testSimple() {
        $map = ZMLocaleUtils::buildL10nMap(self::$DATA_DIR.'l10n-simple', '.phpx');
        if ($this->assertTrue(2 == count($map))) {
            $this->assertEqual(array('Bar' => 'Bar'), array_pop($map));
            $this->assertEqual(array('Yooo' => 'Yooo'), array_pop($map));
        }
    }

    /**
     * Test mixed
     */
    public function testMixed() {
        $map = ZMLocaleUtils::buildL10nMap(self::$DATA_DIR.'l10n-mixed', '.phpx');
        if ($this->assertTrue(1 == count($map))) {
            $this->assertEqual(array('Yooo' => 'Yooo', 'Bar' => 'Bar', 'Foo %s Deng' => 'Foo %s Deng'), array_pop($map));
        }
    }

}
