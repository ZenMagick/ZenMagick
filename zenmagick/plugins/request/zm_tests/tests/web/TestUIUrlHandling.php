<?php

/**
 * Test UI url handling.
 *
 * @package org.zenmagick.plugins.zm_tests.tests
 * @author DerManoMann
 * @version $Id$
 */
class TestUIUrlHandling extends ZMTestCase {

    /**
     * Test zen_href_link.
     */
    public function testZenCartHref() {
        $href = zen_href_link('ipn_main_handler.php', '', 'SSL', false, false, true);
        $expected = HTTPS_SERVER . DIR_WS_HTTPS_CATALOG . 'ipn_main_handler.php';
        $this->assertEqual($expected, $href);
    }

    /**
     * Test admin zen_href_link.
     */
    public function testZenCartAdminHref() {
        ZMSettings::set('isAdmin', true);
        $href = zen_href_link('ipn_main_handler.php', '', 'SSL', false, false, true);
        $expected = HTTPS_SERVER . DIR_WS_HTTPS_CATALOG . 'ipn_main_handler.php';
        $this->assertEqual($expected, $href);
        ZMSettings::set('isAdmin', false);
    }

    /**
     * Test SEO zen_href_link.
     */
    public function testZenCartSEOHref() {
        $href = zen_href_link('ipn_main_handler.php', '', 'SSL', false, false, true);
        $expected = HTTPS_SERVER . DIR_WS_HTTPS_CATALOG . 'ipn_main_handler.php';
        $this->assertEqual($expected, $href);
    }

}

if (!function_exists('zm_build_seo_href')) {
    /**
     * Simulate SEO skipping page.
     * @package org.zenmagick.plugins.zm_tests.tests
     */
    function zm_build_seo_href($page, $parameters, $isSecure) {
        return ZMToolbox::instance()->net->_zm_zen_href_link($page, $parameters, $isSecure ? 'SSL' : 'NONSSL');
    }
}

?>
