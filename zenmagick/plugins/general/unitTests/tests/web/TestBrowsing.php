<?php

/**
 * Storefront browsing testing.
 *
 * @package org.zenmagick.plugins.unitTests.tests
 * @author DerManoMann
 * @version $Id: TestBrowsing.php 2560 2009-11-02 20:08:36Z dermanomann $
 */
class TestBrowsing extends ZMWebTestCase {
    
    /**
     * Test homepage.
     */
    public function testHome() {
        $this->get(ZMRequest::instance()->getToolbox()->net->url(FILENAME_DEFAULT, '', false, false), array('themeId' => 'default'));
        $this->assertResponse(200);
        $this->assertTitle('ZenMagick');
        $this->assertText('Welcome to your new ZenMagick powered online store!');
    }

    /**
     * Test product page.
     */
    public function testProduct() {
        $this->get(ZMRequest::instance()->getToolbox()->net->url(FILENAME_PRODUCT_INFO, '', false, false), array('products_id' => '19', 'themeId' => 'default'));
        $this->assertResponse(200);
        $this->assertTitle('There\'s Something About Mary Linked [DVD-TSAB] :: ZenMagick');
    }

    /**
     * Test contact us page.
     */
    public function testContactUs() {
        $this->get(ZMRequest::instance()->getToolbox()->net->url(FILENAME_CONTACT_US, '', false, false), array('themeId' => 'default'));
        $this->assertResponse(200);
        $this->assertTitle('Contact Us :: ZenMagick');
        $this->assertText(' > Contact Us');
    }

}

?>
