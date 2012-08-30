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

use zenmagick\base\Beans;

/**
 * Price range filter for products.
 *
 * <p>This is an example for a multi value filter.</p>
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class ZMPriceRangeFilter extends ZMResultListFilter {
    private $ranges_;


    /**
     * Create new instance.
     */
    public function __construct() {
        parent::__construct('prfilter', _zm('Price Range'));

        $this->ranges_ = array();
        if (!empty($this->filterValues_) && is_array($this->filterValues_)) {
            // values are in the form of from-to
            foreach ($this->filterValues_ as $value) {
                $range = explode('-', $value);
                if (!empty($value)) {
                    $this->ranges_[$value] = $range;
                }
            }
        }
    }


    /**
     * Return <code>true</code> if the given object is to be excluded.
     *
     * @param mixed obj The obecjt to examine.
     * @return boolean <code>true</code> if the object is to be excluded, <code>false</code> if not.
     */
    function exclude($obj) {
        if (0 == count($this->ranges_)) return false;
        foreach ($this->ranges_ as $range) {
          if ($range[0] < $obj->getPrice() && $obj->getPrice() <= $range[1])
              return false;
        }

        // exclude
        return true;
    }

    /**
     * Returns a list of all available filter values.
     *
     * @return array An array of string values.
     */
    function getOptions() {
        // get all prices
        $prices = array();
        $lowest = 100000;
        $highest = 0;
        foreach ($this->list_->getAllResults() as $result) {
            $price = $result->getPrice();
            // get lowest/highest price
            $lowest = $lowest < $price ? $lowest : $price;
            $highest = $highest >= $price ? $highest : $price;
        }

        //echo $lowest . " - " . $highest;

        // get about 8 ranges
        $diff = ($highest-$lowest) / 8;
        // buld options list
        $options = array();
        $start = 0;
        $toolbox = $this->container->get('request')->getToolbox();
        for ($ii=0; $ii < 8; $ii++ ) {
            $from = $start;
            $to = $start + $diff;
            $start += $diff;
            $name = $toolbox->utils->formatMoney($from) . ' - ' . $toolbox->utils->formatMoney($to);
            $key = $from.'-'.$to;
            $option = Beans::getBean('ZMFilterOption');
            $option->setName($name);
            $option->setKey($key);
            $option->setActive(array_key_exists($key, $this->ranges_));
            $options[$option->getId()] = $option;
        }

        return $options;
    }

    /**
     * Returns <code>true</code> if this filter supports multiple values as filter value.
     *
     * @return boolean <code>true</code> if multiple filter values are supported, <code>false</code> if not.
     */
    function isMultiSelection() {
      return true;
    }

}

?>
