<?php

/**
 * Test query pager.
 *
 * @package org.zenmagick.plugins.unitTests.tests
 * @author DerManoMann
 * @version $Id$
 */
class TestZMQueryPager extends ZMTestCase {

    /**
     * Test simple.
     */
    public function testSimple() {
        $queryPager = new ZMQueryPager(new ZMQueryDetails(Runtime::getDatabase(), 'select * from '.TABLE_PRODUCTS, array(), TABLE_PRODUCTS, 'Product'));
        $queryPager->getResults(2, 13);
    }

    /**
     * Test sql aware.
     *
     * @todo create reliable test data
     */
    public function testSQLAware() {
            $resultList = new ZMResultList();
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
