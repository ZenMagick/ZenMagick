<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2010 zenmagick.org
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

/**
 * Test the product finder.
 *
 * @package org.zenmagick.plugins.unitTests.tests.misc
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
