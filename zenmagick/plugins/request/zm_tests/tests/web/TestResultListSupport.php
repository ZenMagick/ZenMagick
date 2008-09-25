<?php

/**
 * Test result list related code.
 *
 * @package org.zenmagick.plugins.zm_tests.tests
 * @author DerManoMann
 * @version $Id$
 */
class TestResultListSupport extends ZMTestCase {

    /**
     * Test search source.
     */
    public function testSearchSourceOnly() {
        $criteria = ZMLoader::make('ZMSearchCriteria');
        $criteria->setCategoryId(3);
        $criteria->setIncludeSubcategories(true);
        $criteria->setPriceFrom(20);
        $criteria->setKeywords('dvd');

        $source = ZMLoader::make('ZMSearchResultSource', $criteria);
        $results = $source->getResults();
        $this->assertNotNull($results);

        $expectedIds = array(20, 5, 16, 12, 11, 15, 13, 14, 18, 17, 6, 4, 19, 10, 9, 7, 8);

        $this->assertEqual(count($expectedIds), count($results));
        $resultIds = array();
        foreach ($results as $product) {
            $resultIds[] = $product->getId();
        }

        $this->assertEqual($expectedIds, $resultIds);
    }

    /**
     * Test search source with result list.
     */
    public function testSearchSourceWithResultList() {
        $criteria = ZMLoader::make('ZMSearchCriteria');
        $criteria->setCategoryId(3);
        $criteria->setIncludeSubcategories(true);
        $criteria->setPriceFrom(20);
        $criteria->setKeywords('dvd');

        $resultList = ZMLoader::make('ZMResultList');
        $source = ZMLoader::make('ZMSearchResultSource', $criteria);
        $source->setResultList($resultList);
        $results = $source->getResults();
        $this->assertNotNull($results);

        $expectedIds = array(20, 5, 16, 12, 11, 15, 13, 14, 18, 17, 6, 4, 19, 10, 9, 7, 8);

        $this->assertEqual(count($expectedIds), count($results));
        $resultIds = array();
        foreach ($results as $product) {
            $resultIds[] = $product->getId();
        }

        $this->assertEqual($expectedIds, $resultIds);
    }

    /**
     * Test search source with result list and sorter.
     */
    public function testSearchSourceWithResultListAndSorter() {
        $criteria = ZMLoader::make('ZMSearchCriteria');
        $criteria->setCategoryId(3);
        $criteria->setIncludeSubcategories(true);
        $criteria->setPriceFrom(20);
        $criteria->setKeywords('dvd');

        $resultList = ZMLoader::make('ZMResultList');
        $sorter = ZMLoader::make('ZMProductSorter');
        $sorter->setSortId('name');
        $sorter->setDescending(true);
        $resultList->addSorter($sorter);
        $source = ZMLoader::make('ZMSearchResultSource', $criteria);
        $source->setResultList($resultList);
        $results = $source->getResults();
        $this->assertNotNull($results);

        $expectedIds = array(7, 9, 10, 19, 4, 6, 17, 18, 14, 13, 15, 11, 12, 16, 5, 20, 8);

        $this->assertEqual(count($expectedIds), count($results));
        $resultIds = array();
        foreach ($results as $product) {
            $resultIds[] = $product->getId();
        }

        $this->assertEqual($expectedIds, $resultIds);
    }

}

?>
