<?php

/**
 * Test UI url handling.
 *
 * @package org.zenmagick.plugins.zm_tests.tests
 * @author DerManoMann
 * @version $Id$
 */
class TestUIUrlHandling extends UnitTestCase {

    /**
     * Test zen_href_link.
     */
    public function testZenCartHref() {
        $href = zen_href_link('ipn_main_handler.php', '', 'SSL', false, false, true);
        $expected = 'https://localhost/zen-cart/ipn_main_handler.php';
        $this->assertEqual($expected, $href);
    }

    /**
     * Test admin zen_href_link.
     */
    public function testZenCartAdminHref() {
        ZMSettings::set('isAdmin', true);
        $href = zen_href_link('ipn_main_handler.php', '', 'SSL', false, false, true);
        $expected = 'https://localhost/zen-cart/ipn_main_handler.php';
        $this->assertEqual($expected, $href);
        ZMSettings::set('isAdmin', false);
    }

    /**
     * Test SEO zen_href_link.
     */
    public function testZenCartSEOHref() {
        $href = zen_href_link('ipn_main_handler.php', '', 'SSL', false, false, true);
        $expected = 'https://localhost/zen-cart/ipn_main_handler.php';
        $this->assertEqual($expected, $href);
    }

}

/**
 * Simulate SEO skipping page.
 */
function zm_build_seo_href($page, $parameters, $isSecure) {
    return ZMToolbox::instance()->net->_zm_zen_href_link($page, $parameters, $isSecure ? 'SSL' : 'NONSSL');
}

?>
