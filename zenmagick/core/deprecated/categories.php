<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2008 ZenMagick
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
     * <p>Supports show category count and use category page.</p>
     *
     * <p>Links in the active path (&lt;a&gt;) will have a class named <code>act</code>,
     * empty categories will have a class <code>empty</code>. Note that both can occur
     * at the same time.</p>
     *
     * <p>Uses output buffering for increased performance.</p>
     *
     * @package org.zenmagick.deprecated
     * @param array categories An <code>array</code> of <code>ZMCategory</code> instances.
     * @param boolean showProductCount If true, show the product count per category.
     * @param boolean $useCategoryPage If true, create links for empty categories.
     * @param boolean activeParent If true, the parent category is considered in the current category path.
     * @param boolean root Flag to indicate the start of the recursion (not required to set, as defaults to <code>true</code>).
     * @param array path The active category path.
     * @return string The given categories as nested unordered list.
     * @deprecated use the new toolbox instead!
     */
    function zm_build_category_tree_list($categories, $showProductCount=false, $useCategoryPage=false, $activeParent=false, $root=true, $path=null) {
        return ZMToolbox::instance()->macro->categoryTree($categories, $showProductCount, $useCategoryPage);
    }

?>
