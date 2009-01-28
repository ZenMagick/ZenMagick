<?php

/**
 * Test UI url handling.
 *
 * @package org.zenmagick.plugins.zm_tests.tests
 * @author DerManoMann
 * @version $Id$
 */
class TestUIUrlHandling extends ZMTestCase {
    public static $SIMULATE_SEO = false;


    /**
     * {@inheritDoc}
     */
    public function setUp() {
        parent::setUp();
        TestUIUrlHandling::$SIMULATE_SEO = false;
    }

    /**
     * Test zen_href_link.
     */
    public function testZenCartHref() {
        // no context
        $href = zen_href_link('zpn_main_handler.php', '', 'SSL', false, false, true, true);
        $expected = HTTPS_SERVER . DIR_WS_HTTPS_CATALOG . 'zpn_main_handler.php';
        $this->assertEqual($expected, $href);

        $href = zen_href_link(FILENAME_DEFAULT);
        $expected = HTTP_SERVER . DIR_WS_HTTPS_CATALOG . 'index.php?main_page=index';
        $this->assertEqual($expected, $href);
    }

    /**
     * Test admin zen_href_link.
     */
    public function testZenCartAdminHref() {
        ZMSettings::set('isAdmin', true);
        $href = zen_href_link('orders.php', '', 'SSL', false, true, false, true);
        // DIR_WS_ADMIN is not defined here
        $expected = HTTPS_SERVER . 'DIR_WS_ADMINorders.php';
        $this->assertEqual($expected, $href);
        ZMSettings::set('isAdmin', false);
    }

    /**
     * Test SEO zen_href_link.
     */
    public function testZenCartSEOHref() {
        TestUIUrlHandling::$SIMULATE_SEO = true;
        $href = zen_href_link(FILENAME_PRODUCT_INFO, '&products_id=1', 'SSL', false, true, false, true);
        $expected = HTTPS_SERVER . DIR_WS_HTTPS_CATALOG . 'product_info';
        $this->assertEqual($expected, $href);
        TestUIUrlHandling::$SIMULATE_SEO = false;
    }

}

if (!function_exists('zm_build_seo_href')) {
    /**
     * Simulate SEO skipping page.
     * @package org.zenmagick.plugins.zm_tests.tests
     */
    function zm_build_seo_href($page, $parameters, $isSecure, $addSessionId=true, $seo=true, $isStatic=false, $useContext=true) {
        if (TestUIUrlHandling::$SIMULATE_SEO) {
            if ($isSecure) {
                $url = HTTPS_SERVER . DIR_WS_HTTPS_CATALOG . $page;
            } else {
                $url = HTTP_SERVER . DIR_WS_CATALOG . $page;
            }
            return str_replace('.php', '', $url);
        } else {
            return ZMToolbox::instance()->net->furl($page, $parameters, $isSecure ? 'SSL' : 'NONSSL', $addSessionId, false, $isStatic, $useContext);
        }
    }
}

?>
