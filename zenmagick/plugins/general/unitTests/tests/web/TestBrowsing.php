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
 * Storefront browsing testing.
 *
 * @package org.zenmagick.plugins.unitTests.tests
 * @author DerManoMann
 * @version $Id$
 */
class TestBrowsing extends ZMWebTestCase {
    
    /**
     * Test homepage.
     */
    public function testHome() {
        $this->get($this->getRequest()->getToolbox()->net->url(FILENAME_DEFAULT), array('themeId' => 'default'));
        $this->assertResponse(200);
        $this->assertTitle('ZenMagick');
        $this->assertText('Welcome to your new ZenMagick powered online store!');
    }

    /**
     * Test product page.
     */
    public function testProduct() {
        $this->get($this->getRequest()->getToolbox()->net->url(FILENAME_PRODUCT_INFO), array('products_id' => '19', 'themeId' => 'default'));
        $this->assertResponse(200);
        $this->assertTitle('There\'s Something About Mary Linked [DVD-TSAB] :: ZenMagick');
    }

    /**
     * Test contact us page.
     */
    public function testContactUs() {
        $this->get($this->getRequest()->getToolbox()->net->url(FILENAME_CONTACT_US), array('themeId' => 'default'));
        $this->assertResponse(200);
        $this->assertTitle('Contact Us :: ZenMagick');
        $this->assertText(' > Contact Us');
    }

}

?>
