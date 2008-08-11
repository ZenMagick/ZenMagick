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
     * Test homepage.
     */
    public function testHomepage() {
        $this->get(ZMToolbox::instance()->net->url(FILENAME_DEFAULT, '', $this->secure, false));
        $this->assertText('ZenMagick');
    }
}

?>
