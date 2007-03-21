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
    $zm_dtree_catalog_map = array();

    // recursive function to create the JS calls
    function zm_catalog_catalog_tree($categories, $index=1, $level=0, $parent=0, $urlPrefix='#', $showProducts) {
    global $zm_products, $zm_dtree_catalog_map;
        foreach ($categories as $category) {
            $myIndex = $index;
            $hasProducts = 0 != count($zm_products->getProductIdsForCategoryId($category->getId()));
            $url = ",'".$urlPrefix."?".$category->getPath()."'";
            echo "catalog.add(".$index++.",".$parent.",'".$category->getName().(($hasProducts&&$showProducts)?"&gt;":"")."'".$url.",'','','includes/dtree/img/folder.gif','includes/dtree/img/folderopen.gif');\n";
            $zm_dtree_catalog_map[$category->getId()] = $myIndex;
            if ($category->hasChildren()) {
                $index = zm_catalog_catalog_tree($category->getChildren(), $index, $level+1, $myIndex, $urlPrefix, $showProducts);
            }
        }
        return $index;
    }

  function zm_catalog_dtree($pUrlPrefix='#', $cUrlPrefix='#', $folderLinks=false, $showProducts=true) {
  global $zm_categories, $zm_products, $zm_dtree_catalog_map, $zm_request;

      echo '<script type="text/javascript" src="includes/dtree/dtree.js"></script>';
      echo '<script type="text/javascript">';
      echo '  var catalog_state = false;';
      echo '  var catalog = new dTree("catalog");';
      echo '  catalog.add(0,-1,"Catalog");';
      echo '  catalog.config.folderLinks = '.($folderLinks?'true':'false').';';

      $zm_categories->setPath($zm_request->getCategoryPathArray());

      $index = zm_catalog_catalog_tree($zm_categories->getCategoryTree(), 1, 0, 0, $cUrlPrefix, $showProducts); 
      $categoryId = $zm_request->getCategoryId();
      if (0 != $categoryId && $showProducts) {
         $category = $zm_categories->getCategoryForId($categoryId);
         $products = $zm_products->getProductsForCategoryId($categoryId);
         foreach ($products as $product) {
            $url = ",'".$pUrlPrefix."?cPath=".$zm_request->getCategoryPath()."&amp;productId=".$product->getId()."'";
            $zm_dtree_catalog_map['p'.$product->getId()] = $index;
            echo "catalog.add(".$index++.",".$zm_dtree_catalog_map[$categoryId].",'".addslashes($product->getName())."'".$url.",'','','includes/dtree/img/globe.gif');\n";
         }
      }

      echo '  document.write(catalog);';
      echo '  catalog.closeAll();';
      foreach ($zm_request->getCategoryPathArray() as $categoryId) {
          echo "catalog.openTo(".$zm_dtree_catalog_map[$categoryId].", true);";
      }
      $productId = $zm_request->getProductId();
      if (0 != $productId && $showProducts) {
          echo "catalog.openTo(".$zm_dtree_catalog_map['p'.$productId].", true);";
      }
      echo '</script>';
  }
?>
