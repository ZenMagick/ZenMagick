<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2012 zenmagick.org
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

use zenmagick\base\ZMObject;

/**
 * Custom google analytics.
 *
 * @author DerManoMann
 */
class CustomGoogleAnalytics extends ZMObject {

    /**
     * Get pageview string.
     *
     * @param ZMRequest request The current request.
     * @return string A string to identify the current page or <code>null</code> to default to the <em>pagename</em> format.
     */
    public function getPageview($request) {
        $languageId = $request->getSession()->getLanguageId();
        $path = array();
        if (in_array($request->getRequestId(), array('product_info', 'category', 'category_list'))) {
            // category path
            foreach ($request->getCategoryPathArray() as $categoryId) {
                $category = $container->get('categoryService')->getCategoryForId($categoryId, $languageId);
                $path[] = $category->getName();
            }
        }
        if ('product_info' == $request->getRequestId()) {
            // product
            $product = $container->get('productService')->getProductForId($request->getProductId());
            $path[] = $product->getName();
        }

        if (0 < count($path)) {
            foreach ($path as $ii => $elem) {
                // strip unwanted chars
                $elem = str_replace(array('?', '#'), '', $elem);
                // replace whitespace with '-'
                $elem = str_replace(array(' ', "\t"), '-', $elem);
                $path[$ii] = $elem;
            }
            return implode('/', $path);
        }

        return null;
    }

}
