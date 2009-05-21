<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2009 ZenMagick
 *
 * Portions Copyright (c) 2003 The zen-cart developers
 * Portions Copyright (c) 2003 osCommerce
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
 * Ajax controller for JSON checkout.
 *
 * @author DerManoMann
 * @package org.zenmagick.rp.ajax.controller
 * @version $Id$
 * @todo implement!
 */
class ZMAjaxCheckoutController extends ZMAjaxController {

    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct('ajaxCheckout');
        $this->set('ajaxShippingMethodMap', array(
            'id', 'name', 'cost'
        ));
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Get all available shipping methods.
     *
     * <p>Parameters may be either an <code>addressId</code> (only accepted if owned by the current user),
     * or any property of an address (<em>countryId, zoneId, postcode, etc.</em>).</p>
     */
    public function getShippingMethodsJSON() {
        // try to set up an address using request information
        $address = null;
        if (null !== ($addressId = ZMRequest::getParameter('addressId'))) {
            $address = ZMAddresses::instance()->getAddressForId($addressId, ZMRequest::getAccountId());
        } else {
            $data = array();
            foreach (array('countryId', 'zoneId', 'state', 'suburb', 'postcode', 'city') as $property) {
                if (null !== ($value = ZMRequest::getParameter($property))) {
                    $data[$property] = $value;
                }
            }
            if (0 < count($data)) {
                $address = ZMBeanUtils::map2obj('Address', $data);
            }
        }

        if (null == $address) {
            // TODO
            // use defaults from cart
        }

        $shippingMethods = array();
        if (null != $address) {
            foreach (ZMShippingProviders::instance()->getShippingProviders(true) as $provider) {
                foreach ($provider->getShippingMethods($address) as $shippingMethod) {
                    $shippingMethods[] = $shippingMethod;
                }
            }
        }

        $flatObj = $this->flattenObject($shippingMethods, $this->get('ajaxShippingMethodMap'));
        $json = $this->toJSON($flatObj);
        $this->setJSONHeader($json);
    }

}

?>
