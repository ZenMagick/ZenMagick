<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2009 ZenMagick
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
    $currentProduct = ZMProducts::instance()->getProductForId($request->getProductId());
?>

  <h2>Product Associations for &lsquo;<?php echo $currentProduct->getName() ?>&rsquo;</h2>

  <form action="<?php echo $toolbox->admin->url(null, $defaultUrlParams) ?>" method="POST">
    <input type="hidden" name="fkt" value="MerchandizingAssociationsAdminTab">
    <select name="type">
        <option value="xsell">Cross-Sell</option>
    </select>

  </form>

  <a href="#TB_inline?height=455&amp;width=660&amp;inlineId=product-picker&amp;modal=true" class="thickbox">Show hidden modal content.</a>

  <div id="product-picker" style="display:none;">
    <div id="picker-catalog-tree"><?php echo zm_catalog_tree(ZMCategories::instance()->getCategoryTree($session->getLanguageId()), '', false, false, 'picker-tree'); ?></div>
    <div id="picker-data">
       <div id="picker-prod-loading" style="display:none;"><img src="content/jquery/images/loadingAnimation.gif" title="loading..." alt="loading..."></div>
      <div id="picker-prod-list">
      </div>
      <div id="picker-pages"></div>
      <div id="picker-selected"></div>
      <div id="picker-buttons">
        <a class="btn" href="#" onclick="productPicker.close();return false;">OK</a>
        <a class="btn" href="#" onclick="productPicker.cancel();return false;">Cancel</a>
      </div>
    </div>
  </div>
  <script type="text/javascript" src="content/jquery/productPicker.js"></script>
  <script type="text/javascript">
      var ajaxCatalogBaseUrl = '<?php echo $net->ajax('catalog', 'getProductsForCategoryId') ?>';
      var productPicker = new ProductPicker('picker-tree', false, ajaxCatalogBaseUrl, function(productIds) {
          alert('selected: ' + productIds);
      });
      productPicker.init();
  </script>

