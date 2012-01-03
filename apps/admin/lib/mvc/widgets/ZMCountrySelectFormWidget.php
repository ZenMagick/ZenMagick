<?php
/*
 * ZenMagick - Another PHP framework.
 * Copyright (C) 2006-2011 zenmagick.org
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

use zenmagick\http\widgets\form\SelectFormWidget;

/**
 * <p>A country select form widget.</p>
 *
 * @author DerManoMann <mano@zenmagick.org>
 * @package zenmagick.store.admin.mvc.widgets
 */
class ZMCountrySelectFormWidget extends SelectFormWidget {

    /**
     * Create new instance.
     */
    public function __construct() {
        parent::__construct();
    }


    /**
     * {@inheritDoc}
     */
    public function getOptions($request) {
        $options = parent::getOptions($request);
        foreach ($this->container->get('countryService')->getCountries() as $country) {
            $options[$country->getId()] = $country->getName();
        }
        return $options;
    }

}
