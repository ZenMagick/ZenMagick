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
namespace ZenMagick\StoreBundle\Tests\Services;

use ZenMagick\ZenMagickBundle\Test\BaseTestCase;

/**
 * Test <code>Banners</code>.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class BannerServiceTest extends BaseTestCase
{
    /**
     * Test get banner group ids.
     */
    public function testGetBannerGroupIds()
    {
        $groupIds = $this->get('bannerService')->getBannerGroupIds();
        $this->assertEquals(array('BannersAll', 'SideBox-Banners', 'Wide-Banners'), $groupIds);
    }

    /**
     * Test get banners for group name.
     */
    public function testGetBannersForGroupName()
    {
        $banners = $this->get('bannerService')->getBannersForGroupName(null);
        $this->assertEquals(array(), $banners);

        $banners = $this->get('bannerService')->getBannersForGroupName('BannersAll', null, true);
        $this->assertEquals(2, count($banners));
        // check ids
        $bannerIds = array();
        foreach ($banners as $banner) {
            $bannerIds[] = $banner->getId();
        }
        $this->assertEquals(array(5, 9), $bannerIds);
    }

    /**
     * Test enabling and disabling all banners scheduled.
     */
    public function testBannerScheduling()
    {
        $this->markTestIncomplete('skip until we have have better test data');
    }

    /**
     * Test update banner click count.
     */
    public function testBannerUpdateClickCount()
    {
        $this->markTestIncomplete('skip until we have have better test data');
    }

}
