<?php
/*
 * ZenMagick - Extensions for zen-cart
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
 * Test shipping provider service.
 *
 * @package org.zenmagick.plugins.unitTests.tests
 * @author DerManoMann
 * @version $Id$
 */
class TestZMShippingProviders extends ZMTestCase {

    /**
     * Test get provider.
     */
    public function testGetProvider() {
        $zsp = ZMShippingProviders::instance();
        $providers = $zsp->getShippingProviders(true);
        $address = ZMLoader::make('Address');
        $address->setCountryId(153);
        foreach ($providers as $provider) {
            echo "provider id: ".$provider->getId().", name: ".$provider->getName()."<BR>";
            echo "methods:<br>";
            foreach ($provider->getShippingMethods($address) as $shippingMethod) {
                echo $shippingMethod->getId(). ", name: ".$shippingMethod->getName()."<BR>";
                //print_r($shippingMethod->zenMethod_);
                echo "&nbsp; taxRate: ".$shippingMethod->getTaxRate(). ", cost: ".$shippingMethod->getCost()."<BR>";
            }
        }
    }

}

?>
