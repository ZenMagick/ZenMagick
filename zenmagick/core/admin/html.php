<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006,2007 ZenMagick
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
     * Build category tree as simple unordered list.
     *
     * <p>Requires jQuery and the jQuery tree view plugin.</p>
     *
     * @package net.zenmagick.admin
     * @param array categories List of start categories.
     * @param string params Additional parameter; default is ''.
     * @param string id The id of the wrapper div
     * @param boolean root Flag to indicate root level.
     * @return string The created HTML.
     */
    function zm_catalog_tree($categories=array(), $params=null, $showProducts=false, $id='cat-tree', $root=true) {
    global $zm_request, $zm_products, $zm_categories;

        if ($root) { 
            ob_start(); 
            echo <<<EOT
<script type="text/javascript" src="includes/jquery/jquery-1.2.1.pack.js"></script>
<script type="text/javascript" src="includes/jquery/jquery.treeview.pack.js"></script>

<script type="text/javascript">
    $(document).ready(function() {
        $("#cat-tree").treeview({
          collapsed: true,
          unique: true,
          toggle: function() {
            $(".open");
          }
        });
    });
</script>
EOT;
            $zm_categories->setPath($zm_request->getCategoryPathArray());
            $categories = $zm_categories->getCategoryTree();
            echo '<div id="'.$id.'" class="filetree">';
        }
        echo '<ul>';
        $urlBase = basename($PHP_SELF).'?';
        foreach ($categories as $category) {
            $noProducts = count($zm_products->getProductIdsForCategoryId($category->getId(), false));
            $hasProducts = 0 != $noProducts;
            echo '<li class="'.($category->isActive() ? 'open' : '').'">';
            $url = $urlBase.$category->getPath().$params;
            echo '<a href="'.$url.'"><span class="folder">'.zm_htmlencode($category->getName(), false).($hasProducts?'('.$noProducts.')':'').'</span></a>';
            if ($category->hasChildren()) {
                zm_catalog_tree($category->getChildren(), $params, $showProducts, $id, false);
            } else if ($showProducts && $category->isActive()) {
                echo '<ul>';
                foreach ($zm_products->getProductsForCategoryId($category->getId(), false) as $product) {
                    echo '<li><a href=""><span class="file">'.$product->getName().'</span></a></li>';
                }
                echo '</ul>';
            }
            echo '</li>';
        }
        echo '</ul>';

        if ($root) { 
            echo '</div>'; 
            return ob_get_clean();
        }

        return '';
    }


    /**
     * Create a product result list based on the current request.
     *
     * @return string The HTML.
     */
    function zm_product_resultlist() {
    global $zm_request, $zm_products, $zm_loader;

        $resultList = null;
        $products = null;

        if (null != $zm_request->getCategoryPath()) {
            $products = $zm_products->getProductsForCategoryId($zm_request->getCategoryId(), false);
        } else if (null != $zm_request->getManufacturerId()) {
            $products = $zm_products->getProductsForManufacturerId($zm_request->getManufacturerId(), false);
        }

        if (null != $products) {
            $resultList = $zm_loader->create("ProductListResultList", $products, zm_setting('maxProductResultList'));
            ob_start(); 
            echo '<table cellspacing="0" cellpadding="0" class="presults">';
            echo '<thead><tr>';
            echo '<th>'.zm_l10n_get('Name').'</th>';
            echo '<th>'.zm_l10n_get('Active').'</th>';
            echo '</tr></thead>';
            echo '<tbody>';
            $first = true; 
            $odd = true; 
            foreach ($resultList->getResults() as $product) {
                echo '<tr class="'.($odd?"odd":"even").($first?" first":" other").'">';
                echo '<td class="name"><a href="'.zm_href('', $zm_request->getQueryString().'&productId='.$product->getId(), false).'">'.$product->getName().'</a></td>';
                echo '<td class="status">'.($product->getStatus()?zm_l10n_get('yes'):zm_l10n_get('no')).'</td>';
                echo '</tr>';
                $first = false; 
                $odd = !$odd;
            }
            if (1 < $resultList->getNumberOfPages()) {
                echo '<tr class="rnav"><td colspan="2">';
                echo '<span class="pno">'.zm_l10n_get("Page %s/%s", $resultList->getCurrentPageNumber(), $resultList->getNumberOfPages()).'</span>';
                if ($resultList->hasPreviousPage()) {
                    echo '<a href="'.$resultList->getPreviousURL($zm_request->isSecure(), false).'">'.zm_l10n_get("Previous").'</a>&nbsp;';
                } else {
                    echo '<span class="nin">'.zm_l10n_get("Previous").'</span>&nbsp;';
                }
                if ($resultList->hasNextPage()) {
                    echo '<a href="'.$resultList->getNextURL($zm_request->isSecure(), false).'">'.zm_l10n_get("Next").'</a>';
                } else {
                    echo '<span class="nin">'.zm_l10n_get("Next").'</span>';
                }
                echo '</td></tr>';
            }
            echo '</tbody></table>';
            return ob_get_clean();
        }

        return '';
    }

?>
