<?php

/**
 * Test result list related code.
 *
 * @package org.zenmagick.plugins.unitTests.tests
 * @author DerManoMann
 * @version $Id: TestResultListSupport.php 2352 2009-06-29 09:27:55Z dermanomann $
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
        $criteria = new ZMSearchCriteria();
        $criteria->setCategoryId(3);
        $criteria->setIncludeSubcategories(true);
        $criteria->setPriceFrom(20);
        $criteria->setKeywords('dvd');

        $source = new ZMSearchResultSource($criteria);
        $source->setResultList(new ZMResultList());
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
        $criteria = new ZMSearchCriteria();
        $criteria->setCategoryId(3);
        $criteria->setIncludeSubcategories(true);
        $criteria->setPriceFrom(20);
        $criteria->setKeywords('dvd');

        $resultList = new ZMResultList();
        $source = new ZMSearchResultSource($criteria);
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
        $criteria = new ZMSearchCriteria();
        $criteria->setCategoryId(3);
        $criteria->setIncludeSubcategories(true);
        $criteria->setPriceFrom(20);
        $criteria->setKeywords('dvd');

        $resultList = new ZMResultList();
        $sorter = new ZMProductSorter();
        $sorter->setSortId('name');
        $sorter->setDescending(true);
        $resultList->addSorter($sorter);
        $source = new ZMSearchResultSource($criteria);
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

?>
