<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2012 zenmagick.org
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

use ZenMagick\plugins\unitTests\simpletest\TestCase;

/**
 * Test <code>Banners</code>.
 *
 * @package org.zenmagick.plugins.unitTests.tests
 * @author DerManoMann <mano@zenmagick.org>
 */
class TestBanners extends TestCase {

    /**
     * Test get banner group ids.
     */
    public function testGetBannerGroupIds() {
        $groupIds = $this->container->get('bannerService')->getBannerGroupIds();
        $this->assertEqual(array('BannersAll', 'SideBox-Banners', 'Wide-Banners'), $groupIds);
    }

    /**
     * Test get banners for group name.
     */
    public function testGetBannersForGroupName() {
        $banners = $this->container->get('bannerService')->getBannersForGroupName(null);
        $this->assertEqual(array(), $banners);

        $banners = $this->container->get('bannerService')->getBannersForGroupName('BannersAll', null, true);
        $this->assertEqual(2, count($banners));
        // check ids
        $bannerIds = array();
        foreach ($banners as $banner) {
            $bannerIds[] = $banner->getId();
        }
        $this->assertEqual(array(5, 9), $bannerIds);
    }

    /**
     * Test enabling and disabling all banners scheduled.
     */
    public function testBannerScheduling() {
        $this->skip('skip until we have have better test data');
    }

    /**
     * Test update banner click count.
     */
    public function testBannerUpdateClickCount() {
        $this->skip('skip until we have have better test data');
    }

}
