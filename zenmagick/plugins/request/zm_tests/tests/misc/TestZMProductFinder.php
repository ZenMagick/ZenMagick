<?php

/**
 * Test the product finder.
 *
 * @package org.zenmagick.plugins.zm_tests.tests.misc
 * @author DerManoMann
 * @version $Id$
 */
class TestZMProductFinder extends ZMTestCase {

    /**
     * Test.
     */
    public function test() {
        $criteria = ZMLoader::make('SearchCriteria');
        //$criteria->setIncludeTax(true);
        $criteria->setCategoryId(3);
        $criteria->setIncludeSubcategories(true);
        $criteria->setPriceFrom(20);
        $criteria->setKeywords('dvd');

        $search = ZMLoader::make('ProductFinder');

        // should there be a criteria method to set the currency?
        global $currencies;
        $currencyRate_ = $currencies->get_value($_SESSION['currency']);
        if ($currencyRate_) {
            // adjust currency
            $criteria->setPriceFrom($criteria->getPriceFrom() / $currencyRate_);
            $criteria->setPriceTo($criteria->getPriceTo() / $currencyRate_);
        }
        /*
         */

        $search->setCriteria($criteria);
        $search->setSortId('name');
        $search->setDescending(true);
        $queryDetails = $search->execute();
        $results = $queryDetails->query();
        $productIds = array();
        foreach ($results as $result) {
            $productIds[] = $result['productId'];
        }
        $expected = array(7, 9, 10, 19, 4, 6, 17, 18, 14, 13, 15, 11, 12, 16, 5, 20, 8);
        $this->assertEqual($expected, $productIds);
    }

}

?>
