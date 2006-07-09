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

    /**
     * Build a nested unordered list from the given categories.
     *
     * @package net.radebatz.zenmagick.html.defaults
     * @param array categories An <code>array</code> of <code>ZMCategory</code> instances.
     * @param string id An optional id for the root <code>&lt;ul&gt;</code> tag.
     * @return string The given categories as nested unordered list.
     */
    function zm_build_category_tree_list($categories, $id=null) {
        $html = '';

        $html .= '<ul'.(null != $id ? ' id="'.$id.'"' : '').'>';
        foreach ($categories as $category) {
            $html .= '<li>';
            $html .= '<a href="' .
                        zm_href(FILENAME_DEFAULT, '&'.$category->getPath(), '', false, false) .
                        '">'.$category->getName().'</a>';
            if ($category->hasChildren()) {
                $html .= zm_build_category_tree_list($category->getChildren(), null);
            }
        }
        $html .= '</ul>';

        return $html;
    }

?>
