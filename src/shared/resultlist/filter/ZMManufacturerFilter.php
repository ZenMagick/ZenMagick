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

use ZenMagick\Base\Runtime;
use ZenMagick\Base\Beans;
use ZenMagick\Base\Database\QueryDetails;
use ZenMagick\Base\Database\SqlAware;

/**
 * Filter products by manufacturer.
 *
 * @author DerManoMann
 * @package zenmagick.store.shared.mvc.resultlist.filter
 */
class ZMManufacturerFilter extends ZMResultListFilter implements SqlAware
{
    /**
     * Create new instance.
     */
    public function __construct()
    {
        parent::__construct('mfilter', Runtime::getContainer()->get('translator')->trans('Manufacturer'), Runtime::getContainer()->get('request')->query->get('mfilter'));
    }

    /**
     * Return <code>true</code> if the given object is to be excluded.
     *
     * @param mixed obj The obecjt to examine.
     * @return boolean <code>true</code> if the object is to be excluded, <code>false</code> if not.
     */
    public function exclude($obj)
    {
        return $obj->getManufacturerId() != $this->filterValues[0];
    }

    /**
     * Returns a list of all available filter values.
     *
     * @return array An array of string values.
     */
    public function getOptions()
    {
        $options = array();
        foreach ($this->list->getAllResults() as $result) {
            $manufacturer = $result->getManufacturer();
            if (null != $manufacturer) {
                $option = Beans::getBean('ZMFilterOption');
                $option->setId($manufacturer->getId());
                $option->setName($manufacturer->getName());
                $option->setActive($manufacturer->getId() == $this->filterValues[0]);
                $options[$option->getId()] = $option;
            }
        }

        return $options;
    }

    /**
     * {@inheritDoc}
     */
    public function getQueryDetails($method=null, $args=array())
    {
        return new QueryDetails(\ZMRuntime::getDatabase(), 'p.manufacturers_id = '.(int) $this->getValue());
    }

}
