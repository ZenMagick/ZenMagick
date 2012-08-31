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
use ZenMagick\Base\Runtime;
use ZenMagick\Http\Request;
use ZenMagick\Http\forms\FormData;

/**
 * An account form (bean).
 *
 * @author DerManoMann
 * @package zenmagick.store.shared.model.forms
 */
class ZMAccountForm extends FormData {

    /**
     * {@inheritDoc}
     */
    public function populate(Request $request) {
        $this->loadAccount();
        parent::populate($request);
    }

    /**
     * Init with current account settings.
     */
    private function loadAccount() {
        // prepopulate with current account
        $account = $this->container->get('request')->getAccount();
        // move into Beans to wrap unsets of propertynames, attachedM, etc.
        $map = Beans::obj2map($account);
        // TODO: all this should be in a base class (but not ZMModel) - perhaps FormData in rp?
        // also, it should be possible/required to specify the fields that should be merged, plus
        // table names for custom fields (how could we find that out automatically??)
        unset($map['propertyNames']);
        unset($map['password']);
        unset($map['attachedMethods']);
        Beans::setAll($this, $map);
    }

    /**
     * Get a populated <code>ZMAccount</code> instance.
     *
     * @return ZMAccount An account.
     */
    public function getAccount() {
        $account = Beans::getBean('ZMAccount');
        $properties = $this->getProperties();

        // TODO: see comment in c'tor
        // don't need these
        foreach (array(Runtime::getSettings()->get('zenmagick.http.request.idName'), 'formId', 'action') as $name) {
            unset($properties[$name]);
        }

        // special treatment
        $properties['dob'] = DateTime::createFromFormat($this->container->get('localeService')->getFormat('date', 'short'), $properties['dob']);

        $account = Beans::setAll($account, $properties);
        return $account;
    }

}
