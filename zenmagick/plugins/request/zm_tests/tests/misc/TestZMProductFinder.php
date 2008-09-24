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
        //$criteria->populate();
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
        $search->setDecending(true);
        $productIds = $search->execute();
        var_dump($productIds);
    }

}

?>
