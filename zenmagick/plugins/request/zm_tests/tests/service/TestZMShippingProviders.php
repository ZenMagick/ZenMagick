<?php

/**
 * Test shipping provider service.
 *
 * @package org.zenmagick.plugins.zm_tests.tests
 * @author DerManoMann
 * @version $Id$
 */
class TestZMShippingProviders extends UnitTestCase {

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
