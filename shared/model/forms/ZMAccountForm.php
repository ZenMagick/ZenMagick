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

use zenmagick\base\Runtime;

/**
 * An account form (bean).
 *
 * @author DerManoMann
 * @package zenmagick.store.shared.model.forms
 */
class ZMAccountForm extends ZMFormData {

    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
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
        $account = ZMRequest::instance()->getAccount();
        // move into ZMBeanUtils to wrap unsets of propertynames, attachedM, etc.
        $map = ZMBeanUtils::obj2map($account);
        // TODO: all this should be in a base class (but not ZMModel) - perhaps FormData in rp?
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
        $account = ZMBeanUtils::getBean('Account');
        $properties = $this->properties_;

        // TODO: see comment in c'tor
        // don't need these
        foreach (array(Runtime::getSettings()->get('zenmagick.mvc.request.idName'), 'formId', 'action') as $name) {
            unset($properties[$name]);
        }

        // special treatment
        $properties['dob'] = DateTime::createFromFormat(ZMLocales::instance()->getLocale()->getFormat('date', 'short'), $properties['dob']);

        $account = ZMBeanUtils::setAll($account, $properties);
        return $account;
    }

}
