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
namespace ZenMagick\StoreBundle\Model\Mock;

use ZenMagick\Base\Runtime;
use ZenMagick\StoreBundle\Entity\Address;
/**
 * Mock address.
 *
 * @author DerManoMann
 */
class MockAddress extends Address
{
    /**
     * Create new instance.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setFirstName('foo');
        $this->setLastName('bar');
        $this->setCompany('Doo Ltd.');
        $this->setAddressLine1('1 Street');
        $this->setSuburb('Suburb');
        $this->setPostcode('1234');
        $this->setCity('The City');
        $this->setState('Some State');
        $this->setCountry(Runtime::getContainer()->get('countryService')->getCountryForId('NZ'));
    }

}
