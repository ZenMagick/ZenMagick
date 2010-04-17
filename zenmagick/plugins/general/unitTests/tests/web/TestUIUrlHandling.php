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
 * Test UI url handling.
 *
 * @package org.zenmagick.plugins.unitTests.tests
 * @author DerManoMann
 * @version $Id$
 */
class TestUIUrlHandling extends ZMTestCase {

    /**
     * Test zen_href_link #1.
     */
    public function testZenCartHref1() {
        // no context
        $href = zen_href_link('zpn_main_handler.php', '', 'SSL', false, false, true, true);
        $expected = HTTPS_SERVER . DIR_WS_HTTPS_CATALOG . 'zpn_main_handler.php';
        $this->assertEqual($expected, $href);

        $href = zen_href_link(FILENAME_DEFAULT);
        $expected = HTTP_SERVER . DIR_WS_HTTPS_CATALOG . 'index.php?'.ZM_PAGE_KEY.'=index';
        $this->assertEqual($expected, $href);
    }

    /**
     * Test admin zen_href_link.
     */
    public function testZenCartAdminHref() {
        ZMSettings::set('isAdmin', true);
        $href = zen_href_link('orders.php', '', 'SSL', false, true, false, true);
        // DIR_WS_ADMIN is not defined here
        $expected = HTTPS_SERVER . DIR_WS_ADMIN . 'orders.php';
        $this->assertEqual($expected, $href);
        ZMSettings::set('isAdmin', false);
    }

    /**
     * Test SEO zen_href_link.
     */
    public function testZenCartSEOHref() {
        if (null != ($plugin = ZMPlugins::instance()->getPluginForId('useo2')) && $plugin->isEnabled()) {
            $href = zen_href_link(FILENAME_PRODUCT_INFO, '&products_id=1', 'SSL', false, true, false, true);
            $expected = HTTPS_SERVER . DIR_WS_HTTPS_CATALOG . 'matrox-g200-mms-p-1.html';
            $this->assertEqual($expected, $href);
        }
    }

    /**
     * Test zen_href_link #2.
     */
    public function testZenCartHref2() {
        $href = zen_href_link(FILENAME_PRODUCT_INFO, '&products_id=1', 'SSL', false, true, false, true);
        $expected = HTTPS_SERVER . DIR_WS_HTTPS_CATALOG . 'index.php?'.ZM_PAGE_KEY.'=product_info&amp;products_id=1';
        $this->assertEqual($expected, $href);
    }

}
