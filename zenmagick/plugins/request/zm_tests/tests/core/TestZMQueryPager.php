<?php

/**
 * Test query pager.
 *
 * @package org.zenmagick.plugins.zm_tests.tests
 * @author DerManoMann
 * @version $Id$
 */
class TestZMQueryPager extends ZMTestCase {

    /**
     * Test simple.
     */
    public function testSimple() {
        $resultList = ZMLoader::make('ResultList');
        $resultList->setPageNumber(2); 
        $resultList->setPagination(13); 

        $queryPager = ZMLoader::make('QueryPager', ZMLoader::make('QueryDetails', 'select * from '.TABLE_PRODUCTS, array(), TABLE_PRODUCTS, 'Product'));
        $queryPager->getResults($resultList);
    }

    /**
     * Test sql aware.
     *
     * @todo create reliable test data
     */
    public function testSQLAware() {
            $resultList = ZMLoader::make('ZMResultList');
            //$resultList->setResultSource(ZMLoader::make('ObjectResultSource', 'ZMOrder', ZMOrders::instance(), 'getOrdersForStatusId', 1));
            $resultSource = ZMLoader::make('ObjectResultSource', 'ZMOrder', ZMOrders::instance(), 'getAllOrders');
            $resultList->setResultSource($resultSource);
            $sorter = ZMLoader::make('OrderSorter');
            $sorter->setSortId('date');
            $sorter->setDescending(true);
            $queryDetails = $sorter->getQueryDetails();
            $resultList->addSorter($sorter);
            $filter = ZMLoader::make('OrderStatusIdFilter');
            $resultList->addFilter($filter);
            $resultList->setPageNumber(3);
            $orders = $resultList->getResults();
            echo 'is final source: ' . $resultSource->isFinal()."<BR>";
            echo "# of pages: " . $resultList->getNumberOfPages()."<BR>";
            foreach ($orders as $order) {
                echo $order->getId() . ' ' . $order->getOrderDate()."<BR>";
            }
    }

}

?>
