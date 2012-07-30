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
namespace zenmagick\plugins\productTags\tests;

use zenmagick\plugins\unitTests\simpletest\TestCase;

/**
 * Test TagService store implementation.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class TestTagService extends TestCase {

    /**
     * Test getTagsForProductId.
     */
    public function testGetTagsForProductId() {
        $tags = $this->container->get('tagService')->getTagsForProductId(12, 1);
        // test values only
        $this->assertEqual(array('bar', 'foo'), array_values($tags));
    }

    /**
     * Test getProductIdsForTags.
     */
    public function testGetProductIdsForTags() {
        $ids = $this->container->get('tagService')->getProductIdsForTags(array('foo', 'bar'), 1);
        sort($ids);
        $this->assertEqual(array(11, 12, 13), $ids);
    }

    /**
     * Test getAllTags.
     */
    public function testGetAllTags() {
        $tags = $this->container->get('tagService')->getAllTags(1);
        // test values only
        $this->assertEqual(array('bar', 'doh', 'foo'), array_values($tags));
    }

    /**
     * Test setTagsForProductId.
     */
    public function testSetTagsForProductId() {
        $tagService = $this->container->get('tagService');
        $tags = $tagService->setTagsForProductId(12, 1, array('a', 'bar', 'c'));
        $tags = $tagService->getTagsForProductId(12, 1);
        // test values only
        $this->assertEqual(array('a', 'bar', 'c'), array_values($tags));
        // revert
        $tags = $tagService->setTagsForProductId(12, 1, array('foo', 'bar'));
        $tagService->cleanupTags();
    }

    /**
     * Test getStats.
     */
    public function testGetStats() {
        $stats = $this->container->get('tagService')->getStats(1);
        var_dump($stats);
        echo "<BR>";
    }

}
