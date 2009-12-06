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
     * Build category tree as simple unordered list.
     *
     * <p>Requires jQuery and the jQuery tree view plugin.</p>
     *
     * @package net.zenmagick.store.admin
     * @param array categories List of start categories.
     * @param string params Additional parameter; default is ''.
     * @param boolean catUrls Control whether or not to have category urls.
     * @param string id The id of the wrapper div
     * @param boolean root Flag to indicate root level.
     * @param array path The active category path.
     * @return string The created HTML.
     */
    function zm_catalog_tree($categories=array(), $params=null, $showProducts=false, $catUrls=true, $id='cat-tree', $root=true, $path=null) {
        $toolbox = ZMRequest::instance()->getToolbox();
        if ($root) { 
            ob_start(); 
            $path = ZMRequest::instance()->getCategoryPathArray();
            echo '
<script type="text/javascript" src="content/jquery/jquery.treeview.pack.js"></script>
<script type="text/javascript"> $(document).ready(function() { 
  $("#'.$id.'").treeview({ collapsed: true, unique: true, prerendered: false, toggle: function() { $(".open"); } }); 
});
</script>';
            $rootCategories = ZMCategories::instance()->getCategoryTree();
            $root = ZMLoader::make("Category", 0, 0, zm_l10n_get('Catalog'), false);
            foreach ($rootCategories as $rc) {
                $root->addChild($rc);
            }
            $categories = array($root);
            echo '<div id="'.$id.'" class="filetree">';
        }
        echo '<ul>';
        foreach ($categories as $category) {
            $active = in_array($category->getId(), $path);
            $cparams = $params.'&'.$category->getPath();
            $noProducts = count(ZMProducts::instance()->getProductIdsForCategoryId($category->getId(), false));
            $hasProducts = 0 != $noProducts;
            $hasChildren = 0 != count($category->getChildren());
            echo '<li class="'.(($active||0==$category->getId()) ? 'open' : '').'">';
            $url = $catUrls ? ZMRequest::instance()->getToolbox()->admin->url(null, $cparams, true, false) : '#';
            echo '<a class="tree-cat-url c:'.$category->getId().'" href="'.$url.'"><span class="folder">'.$toolbox->html->encode($category->getName(), false).($hasProducts?'('.$noProducts.')':'').'</span></a>';
            if ($category->hasChildren()) {
                zm_catalog_tree($category->getChildren(), $params, $showProducts, $catUrls, $id, false, $path);
            } else if ($showProducts && $active) {
                echo '<ul>';
                    foreach (ZMProducts::instance()->getProductsForCategoryId($category->getId(), false) as $product) {
                        echo '<li><a class="tree-prod-url p:'.$product->getId().'" href="'.$toolbox->admin->url(null, $cparams.'&productId='.$product->getId(), false, false).'"><span class="file">'.$product->getName().'</span></a></li>';
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
     * @package net.zenmagick.store.admin
     * @param string params Additional parameter; default is ''.
     * @return string The HTML.
     */
    function zm_product_resultlist($params='') {
        $toolbox = ZMRequest::instance()->getToolbox();
        $resultList = null;
        $products = null;

        if (null != ZMRequest::instance()->getCategoryPath()) {
            $products = ZMProducts::instance()->getProductsForCategoryId(ZMRequest::instance()->getCategoryId(), false);
        } else if (null != ZMRequest::instance()->getManufacturerId()) {
            $products = ZMProducts::instance()->getProductsForManufacturerId(ZMRequest::instance()->getManufacturerId(), false);
        }
        if (null != $products) {
            $resultList = ZMLoader::make("ResultList");
            $resultList->setPageNumber(ZMRequest::instance()->getPageIndex());
            $resultList->setPagination(16);
            $resultList->setResultSource(ZMLoader::make('ArrayResultSource', 'Product', $products));
            ob_start(); 
            echo '<table cellspacing="0" cellpadding="0" class="presults">';
            echo '<thead><tr>';
            echo '<th class="first">'.zm_l10n_get('Name').'</th>';
            echo '<th class="last status">'.zm_l10n_get('Active').'</th>';
            echo '</tr></thead>';
            echo '<tbody>';
            $first = true; 
            $odd = true; 
            foreach ($resultList->getResults() as $product) {
                echo '<tr class="'.($odd?"odd":"even").($first?" first":" other").'">';
                echo '<td class="first"><a href="'.$toolbox->net->url(null, 'productId='.$product->getId().'&'.$params, false, false).'">'.$product->getName().'</a></td>';
                echo '<td class="last status">'.($product->getStatus()?zm_l10n_get('yes'):zm_l10n_get('no')).'</td>';
                echo '</tr>';
                $first = false; 
                $odd = !$odd;
            }
            if (1 < $resultList->getNumberOfPages()) {
                echo '<tr class="rnav"><td colspan="2">';
                echo '<span class="pno">'.zm_l10n_get("Page %s/%s", $resultList->getPageNumber(), $resultList->getNumberOfPages()).'</span>';
                if ($resultList->hasPreviousPage()) {
                    echo '<a href="'.$toolbox->net->resultListBack($resultList, null, false).'">'.zm_l10n_get("Previous").'</a>&nbsp;';
                } else {
                    echo '<span class="nin">'.zm_l10n_get("Previous").'</span>&nbsp;';
                }
                if ($resultList->hasNextPage()) {
                    echo '<a href="'.$toolbox->net->resultListNext($resultList, null, false).'">'.zm_l10n_get("Next").'</a>';
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
