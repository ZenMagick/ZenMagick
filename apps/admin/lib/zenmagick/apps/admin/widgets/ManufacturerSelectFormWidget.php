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
namespace zenmagick\apps\admin\widgets;

use zenmagick\http\widgets\form\SelectFormWidget;

/**
 * <p>A manufacturer select form widget.</p>
 *
 * <p>This widget will append a list of all available manufacturers to the options list. That
 * means the generic <em>options</em> propert may be used to set custom options that will show
 * up at the top of the list.</p>
 *
 * <p>One typical use is to prepend an empty option if no manufactuer is set/available.</p>
 *
 * <p>Example:</p>
 *
 * <p><code>'ManufacturerSelectFormWidget#title=Manufacturer&options=0= --- '</code></p>
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class ManufacturerSelectFormWidget extends SelectFormWidget {

    /**
     * {@inheritDoc}
     */
    public function getOptions($request) {
        $options = parent::getOptions($request);
        foreach ($this->container->get('manufacturerService')->getManufacturers($request->getSelectedLanguage()->getId()) as $manufacturer) {
            $options[$manufacturer->getId()] = $manufacturer->getName();
        }
        return $options;
    }

}
