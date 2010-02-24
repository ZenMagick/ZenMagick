<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2009 zenmagick.org
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
 * Test <code>ZMProductAssociations</code>.
 *
 * @package org.zenmagick.plugins.zm_token.tests
 * @author DerManoMann
 * @version $Id$
 */
class TestZMProductAssociations extends ZMTestCase {

    /**
     * {@inheritDoc}
     */
    public function setUp() {
        parent::setUp();
        ZMProductAssociations::instance()->registerHandler(new SimpleProductAssociationHandler());
    }

    /**
     * Test simple handler.
     */
    public function testSimpleHandler() {
        $assoc = ZMProductAssociations::instance()->getProductAssociationsForProductId(12, 'simple');
        $this->assertEqual(array(), $assoc);

        $assoc = ZMProductAssociations::instance()->getProductAssociationsForProductId(13, 'foo');
        $this->assertEqual(array(), $assoc);

        $assoc = ZMProductAssociations::instance()->getProductAssociationsForProductId(13, 'simple');
        $this->assertEqual(1, count($assoc));
    }

}

?>
