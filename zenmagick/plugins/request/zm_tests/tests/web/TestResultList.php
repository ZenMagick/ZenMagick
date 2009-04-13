<?php

/**
 * Test simple result list handling.
 *
 * @package org.zenmagick.plugins.zm_tests.tests.misc
 * @author DerManoMann
 * @version $Id$
 */
class TestResultList extends ZMTestCase {

    /**
     * Set up.
     */
    public function setUp() {
        parent::setUp();
        // all tests assume this
        ZMSettings::set('defaultResultListPagination', 10);
    }

    /**
     * Callback to *create* results.
     *
     * @param string resultClass The class of the results; default is <em>ZMProduct</em>.
     * @param int size The number of results to be returned by the source.
     * @return array List of objects of class <em>resultClass</em>.
     */
    public function getResults($resultClass, $size) {
        $results = array();
        for ($ii=0; $ii<$size; ++$ii) {
            $result = ZMLoader::make($resultClass);
            // assume products...
            $result->setId($ii+1);
            $result->setName('product-'.($ii+1));
            $result->setModel('model-'.($ii+1));
            $results[] = $result;
        }
        return $results;
    }

    /**
     * Get a source with the given number of elements.
     *
     * @param int size The number of results to be returned by the source.
     * @param string resultClass The class of the results; default is <em>ZMProduct</em>.
     * @return ZMResultListSource A result list source.
     */
    protected function getResultListSource($size, $resultClass='ZMProduct') {
        // use this::getResults to lookup results
        return ZMLoader::make('ObjectResultSource', $resultClass, $this, 'getResults', array($resultClass, $size));
    }

    /**
     * Test plain.
     */
    public function testPlain() {
        $resultList = new ZMResultList();
        $resultList->setResultSource($this->getResultListSource(13));
        $this->assertEqual(10, $resultList->getPagination());
        $this->assertEqual(13, $resultList->getNumberOfResults());
        $this->assertEqual(2, $resultList->getNumberOfPages());
        $this->assertEqual(10, count($resultList->getResults()));

        $resultList->setPageNumber(2);
        $this->assertEqual(10, $resultList->getPagination());
        $this->assertEqual(13, $resultList->getNumberOfResults());
        $this->assertEqual(2, $resultList->getNumberOfPages());
        $this->assertEqual(3, count($resultList->getResults()));

        $resultList->setPageNumber(3);
        $this->assertEqual(10, $resultList->getPagination());
        $this->assertEqual(13, $resultList->getNumberOfResults());
        $this->assertEqual(2, $resultList->getNumberOfPages());
        $this->assertEqual(3, count($resultList->getResults()));
    }

    /**
     * Test short.
     */
    public function testShort() {
        $resultList = new ZMResultList();
        $resultList->setResultSource($this->getResultListSource(3));
        $this->assertEqual(10, $resultList->getPagination());
        $this->assertEqual(3, $resultList->getNumberOfResults());
        $this->assertEqual(1, $resultList->getNumberOfPages());
        $this->assertEqual(3, count($resultList->getResults()));
    }

    /**
     * Test single page edge.
     */
    public function testSinglePageEdge() {
        $resultList = new ZMResultList();
        $resultList->setResultSource($this->getResultListSource(10));
        $this->assertEqual(10, $resultList->getPagination());
        $this->assertEqual(10, $resultList->getNumberOfResults());
        $this->assertEqual(1, $resultList->getNumberOfPages());
        $this->assertEqual(10, count($resultList->getResults()));
    }

    /**
     * Test multi page edge.
     */
    public function testMultiPageEdge() {
        $resultList = new ZMResultList();
        $resultList->setResultSource($this->getResultListSource(30));
        $resultList->setPageNumber(3);
        $this->assertEqual(10, $resultList->getPagination());
        $this->assertEqual(30, $resultList->getNumberOfResults());
        $this->assertEqual(3, $resultList->getNumberOfPages());
        $this->assertEqual(10, count($resultList->getResults()));
    }

    /**
     * Test empty.
     */
    public function testEmpty() {
        $resultList = new ZMResultList();
        $resultList->setResultSource($this->getResultListSource(0));
        $this->assertEqual(10, $resultList->getPagination());
        $this->assertEqual(0, $resultList->getNumberOfResults());
        $this->assertEqual(0, $resultList->getNumberOfPages());
        $this->assertEqual(0, count($resultList->getResults()));
    }

}

?>
