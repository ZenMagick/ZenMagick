<?php
/**
 * Storefront testing.
 *
 * @package org.zenmagick.plugins.zm_tests.tests
 * @author DerManoMann
 * @version $Id$
 */

class TestBrowsing extends WebTestCase {
    protected $secure = false;
    
    /**
     * Test home.
     */
    public function testHomepage() {
        $this->get(ZMToolbox::instance()->net->url(FILENAME_DEFAULT, 'themeId=default', $this->secure, false));
        $this->assertText('ZenMagick');
    }

    /**
     * Test product page.
     */
    public function testProduct() {
        $this->get(str_replace('&amp;', '&', ZMToolbox::instance()->net->product(19, null, false).'&themeId=default'));
        $this->assertTitle('There\'s Something About Mary Linked [DVD-TSAB] :: ZenMagick');
    }

}

?>
