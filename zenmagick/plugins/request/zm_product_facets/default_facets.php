<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2009 zenmagick.org
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
 *
 * $Id$
 */
?>
<?php

    /**
     * Build manufacturer facet.
     *
     * @package org.zenmagick.plugins.zm_product_facets
     * @param string type The type.
     */
    function zm_build_manufacturer_facet($type=null) {
        $facet = array();
        $manufacturers = ZMManufacturers::instance()->getManufacturers();
        foreach ($manufacturers as $manufacturer) {
            $id = $manufacturer->getId();
            $products = ZMProducts::instance()->getProductsForManufacturerId($id);
            $facet[$id] = array();
            $facet[$id]['id'] = $manufacturer->getId();
            $facet[$id]['name'] = $manufacturer->getName();
            $facet[$id]['entries'] = array();
            foreach ($products as $product) {
                $facet[$id]['entries'][$product->getId()] = $product->getName();
            }
        }

        return $facet;
    }

    /**
     * Build category facet.
     *
     * @package org.zenmagick.plugins.zm_product_facets
     * @param string type The type.
     */
    function zm_build_category_facet($type=null) {
        $facet = array();
        $categories = ZMCategories::instance()->getCategories();
        foreach ($categories as $category) {
            $id = $category->getId();
            $products = ZMProducts::instance()->getProductsForCategoryId($id);
            $facet[$id] = array();
            $facet[$id]['id'] = $category->getId();
            $facet[$id]['name'] = $category->getName();
            $facet[$id]['entries'] = array();
            foreach ($products as $product) {
                $facet[$id]['entries'][$product->getId()] = $product->getName();
            }
        }

        return $facet;
    }

    /**
     * Build price range facet.
     *
     * @package org.zenmagick.plugins.zm_product_facets
     * @param string type The type.
     */
    function zm_build_pricerange_facet($type=null) {
        $facet = array();
        $products = ZMProducts::instance()->getAllProducts();

        $low = 9999999;
        $high = 0;
        foreach ($products as $product) {
            // use product price to be at least a bit faster
            if ($low > $product->getProductPrice()) {
                $low = $product->getProductPrice();
            }
            if ($high < $product->getProductPrice()) {
                $high = $product->getProductPrice();
            }
        }

        // figure out the price brackets; we want about 5 brackets
        $bracketLength = 5;
        $range = $high - $low;
        $width = $range / $bracketLength;

        $scale = 1;
        for ($ii=1; $ii < 5; ++$ii) {
            $bracketSize = round($width / $scale);
            if (abs($bracketSize-$width) < 10) {
                break;
            }
            $scale *= 10;
        }
        $brackets = array();
        for ($ii=0; $ii < $bracketLength; ++$ii) {
            $brackets[$ii] = ($ii+1)*$bracketSize;
        }

        foreach ($brackets as $ii => $bracket) {
            $id = $ii;
            $facet[$id]['entries'] = array();
            $facet[$id] = array();
            $facet[$id]['id'] = $ii;
            if (0 == $ii) {
                // first
                $facet[$id]['name'] = zm_l10n_get("Less than %s", ZMRequest::instance()->getToolbox()->utils->formatMoney($bracket));
                    foreach ($products as $product) {
                        if ($product->getProductPrice() < $bracket) {
                            $facet[$id]['entries'][$product->getId()] = $product->getName();
                        }
                    }
            } else if (($ii+1) == $bracketLength) {
                $prevBracket = $brackets[$ii-1];
                $facet[$id]['name'] = zm_l10n_get("More than %s", ZMRequest::instance()->getToolbox()->utils->formatMoney($prevBracket));
                foreach ($products as $product) {
                    if ($product->getProductPrice() > $prevBracket) {
                        $facet[$id]['entries'][$product->getId()] = $product->getName();
                    }
                }
            } else {
                // all other
                $prevBracket = $brackets[$ii-1];
                $facet[$id]['name'] = zm_l10n_get("%s to %s", ZMRequest::instance()->getToolbox()->utils->formatMoney($prevBracket), ZMRequest::instance()->getToolbox()->utils->formatMoney($bracket));
                foreach ($products as $product) {
                    if ($product->getProductPrice() >= $prevBracket && $product->getProductPrice() <= $bracket) {
                        $facet[$id]['entries'][$product->getId()] = $product->getName();
                    }
                }
            }
        }

        return $facet;
    }

?>
