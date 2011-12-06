<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2011 zenmagick.org
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

use zenmagick\base\Beans;

/**
 * Test result list related code.
 *
 * @package org.zenmagick.plugins.unitTests.tests
 * @author DerManoMann <mano@zenmagick.org>
 */
class TestResultListSupport extends ZMTestCase {

    /**
     * Set up.
     */
    public function setUp() {
        parent::setUp();
        // all tests assume this
        ZMSettings::set('zenmagick.mvc.resultlist.defaultPagination', 10);
    }

    /**
     * Test search source.
     */
    public function testSearchSourceOnly() {
        $criteria = Beans::getBean('ZMSearchCriteria');
        $criteria->setCategoryId(3);
        $criteria->setIncludeSubcategories(true);
        $criteria->setPriceFrom(20);
        $criteria->setKeywords('dvd');

        $source = Beans::getBean('ZMSearchResultSource');
        $source->setSearchCriteria($criteria);
        $source->setResultList(Beans::getBean('ZMResultList'));
        $results = $source->getResults();
        $this->assertNotNull($results);

        $expectedIds = array(20, 5, 16, 12, 11, 15, 13, 14, 18, 17); //, 6, 4, 19, 10, 9, 7, 8);

        $this->assertEqual(17, $source->getTotalNumberOfResults());
        $resultIds = array();
        foreach ($results as $product) {
            $resultIds[] = $product->getId();
        }

        $this->assertEqual($expectedIds, $resultIds);
    }

    /**
     * Test search source with result list.
     */
    public function testSourceWithList() {
        $criteria = Beans::getBean('ZMSearchCriteria');
        $criteria->setCategoryId(3);
        $criteria->setIncludeSubcategories(true);
        $criteria->setPriceFrom(20);
        $criteria->setKeywords('dvd');

        $resultList = Beans::getBean('ZMResultList');
        $source = Beans::getBean('ZMSearchResultSource');
        $source->setSearchCriteria($criteria);
        $source->setResultList($resultList);
        $results = $source->getResults();
        $this->assertNotNull($results);

        $expectedIds = array(20, 5, 16, 12, 11, 15, 13, 14, 18, 17); //, 6, 4, 19, 10, 9, 7, 8);

        $this->assertEqual(17, $source->getTotalNumberOfResults());
        $resultIds = array();
        foreach ($results as $product) {
            $resultIds[] = $product->getId();
        }

        $this->assertEqual($expectedIds, $resultIds);
    }

    /**
     * Test search source with result list and sorter.
     */
    public function testSourceWithListAndSorter() {
        $criteria = Beans::getBean('ZMSearchCriteria');
        $criteria->setCategoryId(3);
        $criteria->setIncludeSubcategories(true);
        $criteria->setPriceFrom(20);
        $criteria->setKeywords('dvd');

        $resultList = Beans::getBean('ZMResultList');
        $sorter = Beans::getBean('ZMProductSorter');
        $sorter->setSortId('name');
        $sorter->setDescending(true);
        $resultList->addSorter($sorter);
        $source = Beans::getBean('ZMSearchResultSource');
        $source->setSearchCriteria($criteria);
        $source->setResultList($resultList);
        $results = $source->getResults();
        $this->assertNotNull($results);

        $expectedIds = array(7, 9, 10, 19, 4, 6, 17, 18, 14, 13);

        $this->assertEqual(17, $source->getTotalNumberOfResults());
        $resultIds = array();
        foreach ($results as $product) {
            $resultIds[] = $product->getId();
        }

        $this->assertEqual($expectedIds, $resultIds);
    }

}
