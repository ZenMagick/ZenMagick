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
     * Test foo.
     */
    public function testFoo() {
        $resultList = ZMLoader::make('ResultList');
        $resultList->setPageNumber(2); 
        $resultList->setPagination(13); 

        $queryPager = ZMLoader::make('QueryPager', ZMLoader::make('QueryDetails', 'select * from '.TABLE_PRODUCTS, array(), TABLE_PRODUCTS, 'Product'));
        $queryPager->getResults($resultList);


    }

}

?>
