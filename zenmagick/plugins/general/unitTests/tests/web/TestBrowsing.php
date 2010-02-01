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
