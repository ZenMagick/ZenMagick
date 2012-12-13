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
namespace ZenMagick\StorefrontBundle\Controller;

use ZenMagick\Base\Beans;

/**
 * Ajax controller for JSON checkout.
 *
 * @author DerManoMann <mano@zenmagick.org>
 * @todo implement!
 */
class AjaxCheckoutController extends \ZMAjaxController
{
    /**
     * Create new instance.
     */
    public function __construct()
    {
        parent::__construct('ajaxCheckout');
        $this->set('ajaxShippingMethodMap', array(
            'id', 'name', 'cost', 'provider' => array(
                'name', 'errors'
            )
        ));
    }

    /**
     * Get all available shipping methods.
     *
     * <p>Request parameter (either or):</p>
     * <ul>
     *  <li>addressId - A valid address id (only accepted if owned by the current user)</li>
     *  <li>Any address proerty (<em>countryId, zoneId, postcode, etc.</em>)</li>
     * </ul>
     *
     * @param ZenMagick\Http\Request request The current request.
     */
    public function getShippingMethodsJSON($request)
    {
        // try to set up an address using request information
        $address = null;
        if (null !== ($addressId = $request->getParameter('addressId'))) {
            $address = $this->container->get('addressService')->getAddressForId($addressId, $this->getUser()->getId());
        } else {
            $data = array();
            foreach (array('countryId', 'zoneId', 'state', 'suburb', 'postcode', 'city') as $property) {
                if (null !== ($value = $request->getParameter($property))) {
                    $data[$property] = $value;
                }
            }
            if (0 < count($data)) {
                $address = Beans::map2obj('Address', $data);
            }
        }

        $shoppingCart = $this->get('shoppingCart');
        if (null == $address) {
            $address = $shoppingCart->getShippingAddress();
        }

        $shippingMethods = array();
        if (null != $address && !$shoppingCart->isEmpty()) {
            foreach ($this->container->get('shippingProviderService')->getShippingProviders(true) as $provider) {
                foreach ($provider->getShippingMethods($shoppingCart, $address) as $shippingMethod) {
                    $shippingMethods[] = $shippingMethod;
                }
            }
        }

        $flatObj = $this->container->get('utilsTool')->flattenObject($shippingMethods, $this->get('ajaxShippingMethodMap'));
        $json = json_encode($flatObj);
        $this->setJSONHeader($json);
    }

}
