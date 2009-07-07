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
 * A registration form (bean).
 *
 * @author DerManoMann
 * @package org.zenmagick.store.model.forms
 * @version $Id: ZMRegistrationForm.php 2113 2009-03-27 02:48:42Z dermanomann $
 */
class ZMRegistrationForm extends ZMFormBean {

    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
        $this->addFields('privacy,gender,firstName,lastName,dob,email,nickName,password,confirmation,companyName,addressLine1,suburb,city,postcode,countryId,zoneId,state,phone,fax,emailFormat,newsletterSubscriber,referral');
        $this->addTables('customers,customers_info');
    }


    /**
     * Get a populated <code>ZMAccount</code> instance.
     *
     * @return ZMAccount An account.
     */
    public function getAccount() {
        $account = ZMLoader::make('Account');
        $properties = $this->properties_;

        // don't need these
        foreach (array(ZM_PAGE_KEY, 'formId', 'action') as $name) {
            unset($properties[$name]);
        }

        // special treatment
        $properties['dob'] = ZMTools::translateDateString($properties['dob'], UI_DATE_FORMAT, ZM_DATETIME_FORMAT);

        ZMBeanUtils::setAll($account, $properties);
        return $account;
    }

    /**
     * Get a populated <code>ZMAddress</code> instance.
     *
     * @return ZMAddress An address.
     */
    public function getAddress() {
        $address = ZMLoader::make('Address');
        $properties = $this->properties_;

        // don't need these
        foreach (array(ZM_PAGE_KEY, 'formId', 'action', 'password', 'confirmation') as $name) {
            unset($properties[$name]);
        }

        // special treatment
        if (empty($properties['countryId'])) {
            $properties['countryId'] = 0;
        }

        ZMBeanUtils::setAll($address, $properties);
        return $address;
    }

}

?>
