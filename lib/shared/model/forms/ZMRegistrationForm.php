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
use ZenMagick\Http\forms\FormData;

/**
 * A registration form (bean).
 *
 * @author DerManoMann
 * @package zenmagick.store.shared.model.forms
 */
class ZMRegistrationForm extends FormData {

    /**
     * Get a populated <code>ZMAccount</code> instance.
     *
     * @return ZMAccount An account.
     */
    public function getAccount() {
        $account = Beans::getBean('ZMAccount');
        $properties = $this->getProperties();

        // don't need these
        foreach (array(Runtime::getSettings()->get('zenmagick.http.request.idName'), 'formId', 'action', 'confirmation') as $name) {
            unset($properties[$name]);
        }

        // special treatment
        $properties['dob'] = DateTime::createFromFormat($this->container->get('localeService')->getFormat('date', 'short'), $properties['dob']);

        $account = Beans::setAll($account, $properties);
        return $account;
    }

    /**
     * Get a populated <code>ZMAddress</code> instance.
     *
     * @return ZMAddress An address.
     */
    public function getAddress() {
        $address = Beans::getBean('ZMAddress');
        $properties = $this->getProperties();

        // don't need these
        foreach (array(Runtime::getSettings()->get('zenmagick.http.request.idName'), 'formId', 'action', 'password', 'confirmation') as $name) {
            unset($properties[$name]);
        }

        // special treatment
        if (empty($properties['countryId'])) {
            $properties['countryId'] = 0;
        }

        $address = Beans::setAll($address, $properties);
        return $address;
    }

}
