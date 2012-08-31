<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2012 zenmagick.org
 *
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

/**
 * Check for either state or zone.
 *
 * <p>This rule will attempt to make changes to the underlying form bean in order to adjust state/zoneId
 * if required.</p>
 *
 * @author DerManoMann
 * @package zenmagick.store.shared.mvc.validation
 */
class ZMStateOrZoneIdRule extends ZMRule {

    /**
     * Create new required rule.
     *
     * @param string name The field name.
     * @param string msg Optional message.
     */
    public function __construct($name, $msg=null) {
        parent::__construct($name, "Please enter a state.", $msg);
    }


    /**
     * Validate the given request data.
     *
     * @param ZenMagick\Http\Request request The current request.
     * @param array data The data.
     * @return boolean <code>true</code> if the value for <code>$name</code> is valid, <code>false</code> if not.
     */
    public function validate($request, $data) {
        if (!Runtime::getSettings()->get('isAccountState')) {
            return true;
        }

        //todo: this should not be here, but in the corresponding controller classes - BEFORE the validation is done
        $state = isset($data['state']) ? $data['state'] : null;
        $zoneId = $data['zoneId'];
        $zones = $this->container->get('countryService')->getZonesForCountryId($data['countryId']);
        $valid = false;
        if (0 < count ($zones)) {
            // need $state to match either an id or name
            foreach ($zones as $zone) {
                if ($zone->getName() == $state || $zone->getId() == $state || $zone->getId() == $zoneId || $zone->getCode() == $state) {
                    $zoneId = $zone->getId();
                    $state = '';
                    $valid = true;
                    break;
                }
            }
        } else {
            if (!empty($state)) {
                $valid = true;
                $zoneId = 0;
            }
        }

        // check for form bean
        if (array_key_exists('__obj', $data)) {
            $data['__obj'] = Beans::setAll($data['__obj'], array('state' => $state, 'zoneId' => $zoneId));
        }

        return $valid;
    }


    /**
     * Create JS validation call.
     *
     * @return string Formatted JavaScript .
     */
    public function toJSString() {
        return '';
    }

}
