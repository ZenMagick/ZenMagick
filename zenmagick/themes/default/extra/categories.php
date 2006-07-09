<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006 ZenMagick
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

    // build a nested unordered list from the given categories
    // based on core/html/html_categories.php
    function buildCategoryTreeList($categories, $activeParent=false, $root=true) {
    global $zm_products;
        $html = '';
        $html .= '<ul' . ($activeParent ? ' class="act"' : '') . '>';
        foreach ($categories as $category) {
            $active = $category->isActive();
            $noOfProducts = count($zm_products->getProductIdsForCategoryId($category->getId()));
            $empty = 0 == $noOfProducts;
            $html .= '<li>';
            $class = '';
            $class = $active ? 'act' : '';
            $class = $empty ? ' empty' : '';
            $class = trim($class);
            $html .= '<a' . ('' != $class ? ' class="'.$class.'"' : '') . ' href="' .
                        zm_href(FILENAME_DEFAULT, '&'.$category->getPath(), '', false, false) .
                        '">'.$category->getName().'</a>';
            if (0 < $noOfProducts) {
                //$html .= ' ('.$noOfProducts.')';
            }
            if ($category->hasChildren()) {
                $html .= '&gt;';
            }
            if ($category->hasChildren()) { // && $active) {
                $html .= buildCategoryTreeList($category->getChildren(), $active, false);
            }
            $html .= '</li>';
        }
        $html .= '</ul>';

        return $html;
    }

?>
