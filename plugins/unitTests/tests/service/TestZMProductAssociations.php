<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2011 zenmagick.org
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
 * @author DerManoMann <mano@zenmagick.org>
 */
class TestZMProductAssociations extends ZMTestCase {

    /**
     * Test simple handler.
     */
    public function testSimpleHandler() {
        $productAssociations = $this->container->get('productAssociations');

        $assoc = $productAssociations->getProductAssociationsForProductId(12, 'simple');
        $this->assertEqual(array(), $assoc);

        $assoc = $productAssociations->getProductAssociationsForProductId(13, 'foo');
        $this->assertEqual(array(), $assoc);

        $assoc = $productAssociations->getProductAssociationsForProductId(13, 'simple');
        $this->assertEqual(1, count($assoc));
    }

}
