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

use ZenMagick\ZenMagickBundle\Test\BaseTestCase;

/**
 * Test ezpages service.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class EZPagesTest extends BaseTestCase
{
    /**
     * Test load.
     */
    public function testLoad()
    {
        $page = $this->get('ezPageService')->getPageForId(8, 1);
        if ($this->assertNotNull($page)) {
            $this->assertEquals(8, $page->getId());
            $this->assertTrue($page->isSidebox());
            $this->assertEquals(40, $page->getSideboxSort());
        }
    }

    /**
     * Test update.
     */
    public function testUpdate()
    {
        $ezPageService = $this->get('ezPageService');

        $page = $ezPageService->getPageForId(8, 1);
        if ($this->assertNotNull($page)) {
            $page->setHeaderSort(33);
            $status = $ezPageService->updatePage($page);
            $this->assertTrue($status);
            $this->assertEquals(33, $page->getHeaderSort());

            // load from scratch
            $page = $ezPageService->getPageForId(8, 1);
            if ($this->assertNotNull($page)) {
                $this->assertEquals(33, $page->getHeaderSort());
            }

            // revert change
            $page->setHeaderSort(0);
            $ezPageService->updatePage($page);
        }
    }

    /**
     * Test create.
     */
    public function testCreate()
    {
        $ezPageService = $this->get('ezPageService');

        // make copy
        $page = $ezPageService->getPageForId(8, 1);
        $newPage = $ezPageService->createPage($page);
        if ($this->assertNotNull($newPage)) {
            $this->assertNotEqual(8, $newPage->getId());
            $ezPageService->removePage($newPage);
        }
    }

}
