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
 * An account form (bean).
 *
 * @author DerManoMann
 * @package org.zenmagick.store.model.forms
 * @version $Id: ZMAccountForm.php 2113 2009-03-27 02:48:42Z dermanomann $
 */
class ZMAccountForm extends ZMFormBean {

    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
        $this->addFields('gender,firstName,lastName,dob,email,nickName,companyName,phone,fax,emailFormat');
        $this->addTables('customers,customers_info');
        $this->loadAccount();
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Init with current account settings.
     */
    private function loadAccount() {
        // prepopulate with current account
        $account = ZMRequest::getAccount();
        // move into ZMBeanUtils to wrap unsets of propertynames, attachedM, etc.
        $map = ZMBeanUtils::obj2map($account);
        // TODO: all this should be in a base class (but not ZMModel) - perhaps ZMFormBean in rp?
        // also, it should be possible/required to specify the fields that should be merged, plus
        // table names for custom fields (how could we find that out automatically??)
        unset($map['propertyNames']);
        unset($map['password']);
        unset($map['attachedMethods']);
        ZMBeanUtils::setAll($this, $map);
    }

    /**
     * Get a populated <code>ZMAccount</code> instance.
     *
     * @return ZMAccount An account.
     */
    public function getAccount() {
        $account = ZMLoader::make('Account');
        $properties = $this->properties_;

        // TODO: see comment in c'tor
        // don't need these
        foreach (array(ZM_PAGE_KEY, 'formId', 'action') as $name) {
            unset($properties[$name]);
        }

        // special treatment
        $properties['dob'] = ZMTools::translateDateString($properties['dob'], UI_DATE_FORMAT, ZM_DATETIME_FORMAT);

        ZMBeanUtils::setAll($account, $properties);
        return $account;
    }

}

?>
