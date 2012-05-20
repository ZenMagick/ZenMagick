<?php
/*
 * ZenMagick - Extensions for zen-cart
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
namespace zenmagick\apps\store\themes\modern;


/**
 * Macro utilities.
 *
 * @author DerManoMann
 * @package org.zenmagick.store.mvc.tools
 */
class ToolboxMacro extends \zenmagick\apps\store\toolbox\ToolboxMacro {

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
     * <p>Please note that the last three parameter are used internally and should not bet set.</p>
     *
     * @param array categories An <code>array</code> of <code>ZMCategory</code> instances.
     * @param boolean showProductCount If true, show the product count per category; default is <code>false</code>.
     * @param boolean $useCategoryPage If true, create links for empty categories; default is <code>false</code>.
     * @param boolean activeParent If true, the parent category is considered in the current category path; default is <code>false</code>.
     * @param boolean root Flag to indicate the start of the recursion (not required to set, as defaults to <code>true</code>); default is <code>true</code>.
     * @param array path The active category path; default is <code>null</code>.
     * @return string The given categories as nested unordered list.
     */
    public function categoryTree($categories, $showProductCount=false, $useCategoryPage=false, $activeParent=false, $root=true, $path=null) {
        $toolbox = $this->getToolbox();
        $languageId = $this->getRequest()->getSession()->getLanguageId();
        $css_root = '';
        if ($root) {
            ob_start();
            $path = $this->getRequest()->getCategoryPathArray();
            $css_root = ' class="root"';
        }

        echo '<ul' . ($activeParent ? ' class="act"' : '') . '>';
        foreach ($categories as $category) {
            if (!$category->isActive()) {
                continue;
            }

            /*if ($root) {
        		echo '<div class="parentCate catFirst"><a href="' .
                        $this->getRequest()->url('category', 'cPath='.implode('_', $category->getPath())).'">'.$toolbox->html->encode($category->getName()).'</a></div>';
        		continue;
        	}*/

            $active = in_array($category->getId(), $path);
            $noOfProductsInCat = $showProductCount ? count($container->get('productService')->getProductIdsForCategoryId($category->getId(), $languageId, true, false)) : 0;
            $isEmpty = 0 == $noOfProductsInCat;
            echo '<li'.$css_root.'>';
            $class = '';
            $class = $active ? 'act' : '';
            $class .= $isEmpty ? ' empty' : '';
            $class .= ($active && !$category->hasChildren()) ? ' curr' : '';
            $class = trim($class);
            $onclick = $isEmpty ? ($useCategoryPage ? '' : ' onclick="return catclick(this);"') : '';
            echo '<a' . ('' != $class ? ' class="'.$class.'"' : '') . $onclick . ' href="' .
                        $this->getRequest()->url('category', 'cPath='.implode('_', $category->getPath())) .
                        '">'.$toolbox->html->encode($category->getName()).'</a>';
            /*if ($showProductCount) {
                if (0 < ($noOfProductsInTree = count($container->get('productService')->getProductIdsForCategoryId($category->getId(), $languageId, true, true)))) {
                    echo '('.$noOfProductsInTree.')';
                }
            }
            if ($category->hasChildren()) {
                echo '&gt;';
            }*/
            if ($category->hasChildren()) {
                $this->categoryTree($category->getChildren(), $showProductCount, $useCategoryPage, $active, false, $path);
            }
            echo '</li>';
        }
        echo '</ul>';

        $html = $root ? ob_get_clean() : '';
        return $html;
    }




}
