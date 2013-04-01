<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2012 zenmagick.org
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

use ZenMagick\Base\Beans;
use ZenMagick\ZenMagickBundle\Test\BaseTestCase;

/**
 * Test shipping provider service.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class TestZMShippingProviders extends BaseTestCase
{
    /**
     * Test get provider.
     */
    public function testGetProvider()
    {
        $el = error_reporting(0);
        $providers = $this->get('shippingProviderService')->getShippingProviders(true);
        $address = Beans::getBean('ZenMagick\StoreBundle\Entity\Address');
        $address->setCountryId(153);
        foreach ($providers as $provider) {
            echo "provider id: ".$provider->getId().", name: ".$provider->getName()."<BR>";
            echo "methods:<br>";
            //TODO: set cart
            foreach ($provider->getShippingMethods(Beans::getBean('shoppingCart'), $address) as $shippingMethod) {
                echo $shippingMethod->getId(). ", name: ".$shippingMethod->getName()."<BR>";
                //print_r($shippingMethod->zenMethod_);
                echo "&nbsp; taxRate: ".$shippingMethod->getTaxRate(). ", cost: ".$shippingMethod->getCost()."<BR>";
            }
        }
        error_reporting($el);
    }

}
