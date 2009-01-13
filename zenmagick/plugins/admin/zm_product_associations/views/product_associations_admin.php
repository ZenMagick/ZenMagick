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
    $toolbox = ZMToolbox::instance();
    $zm_product = ZMProducts::instance()->getProductForId(ZMRequest::getProductId());
?>

  <h2>Product Associations for &lsquo;<?php echo $zm_product->getName() ?>&rsquo;</h2>

  <?php $toolbox->form->open('', $zm_nav_params) ?>
    <?php $types = ZMProductAssociationService::instance()->getAssociationTypes(); ?>
    <select name="type">
      <?php foreach ($types as $type => $name) { ?>
        <option value="<?php echo $type ?>"><?php echo $name ?></option>
      <?php } ?>
    </select>

  </form>

  <a href="#TB_inline?height=455&amp;width=660&amp;inlineId=product-picker&amp;modal=true" class="thickbox">Show hidden modal content.</a>

  <div id="product-picker" style="display:none;">
    <div style="float:left;width:35%;overflow:scroll;height:400px;"><?php  echo zm_catalog_tree(ZMCategories::instance()->getCategoryTree(), '', false, false, 'picker-tree'); ?></div>
    <div style="margin:0 5px 0 36%;;width:60%;height:400px;">
      <div id="picker-prod-list" style="border:1px solid black;margin:5px;height:320px;"></div>
      <div id="picker-pages"></div>
      <div id="picker-selected"></div>
      <div style="text-align:right;padding:5px;">
        <a class="btn" href="#" onclick="productPicker.cancel();return false;">Cancel</a>
        <a class="btn" href="#" onclick="productPicker.close();return false;">OK</a>
      </div>
    </div>
  </div>
  <script type="text/javascript">
      // create picker
      function ProductPicker(id, selectSingle, handler) {
	        this.id = selectSingle;
	        this.selectSingle = selectSingle;
	        this.handler = handler;
          this.products = new Array();
          this.categoryCache = new Array();
      }

      // implementation
      ProductPicker.prototype = {
          // picker closed
          close: function() {
              if (this.handler) {
                  this.handler(this.products);
              }
              this.done();
          },

          cancel: function() {
              this.done();
          },

          done: function() {
              tb_remove();
              $('#picker-prod-list').html('');
              $('#picker-pages').html('');
              this.products = new Array();
          },

          // product selected
          picked: function(elem, productId) {
              //XXX: mark elem as selected?
              //XXX: get product object from cache and store? flag as selected?
           		this.products.push(productId);
              if (this.selectSingle) {
                  this.close();
              }
          },

          displayResults: function(resultList, categoryId) {
              prodList = $('#picker-prod-list');
              prodList.html('');
              var html = '';
              for (var jj=0; jj < resultList.results.length; ++jj) {
                  var item = resultList.results[jj];
                  html += '<a href="#" onclick="productPicker.picked(this, '+item.id+')">'+item.name+'</a><br>';
              }
              prodList.html(html);
              var pages = 'Page ' + resultList.pageNumber + ' of ' + resultList.numberOfPages+':&nbsp;&nbsp;&nbsp;';
              if (1 < resultList.numberOfPages) {
                  // display/update page links
                  for (var kk=1; kk <= resultList.numberOfPages; ++kk) {
                      if (kk == resultList.pageNumber) {
                          pages += '['+kk+']&nbsp;';
                      } else {
                          // XXX: how to avoid using productPicker??
                          pages += '<a href="#" onclick="productPicker.categoryClick('+categoryId+', '+kk+');return false;" >'+kk+'</a>&nbsp;';
                      }
                  }
              }
              $('#picker-pages').html(pages);
          },

          // category clicked
          categoryClick: function(categoryId, page) {
              if (!page) {
                  page = 1;
              }
              var cacheKey = categoryId+'-'+page;
              if (cacheKey in this.categoryCache) {
                  // use cached results
                  this.displayResults(this.categoryCache[cacheKey], categoryId);
                  return;
              }
              var ajaxUrl = '<?php $toolbox->net->ajax('catalog', 'getProductsForCategoryId') ?>'+'&categoryId='+categoryId+'&pagination=16&page='+page;
              var me = this;
              prodList = $('#picker-prod-list');
              prodList.html('Loading...');
              $.getJSON(ajaxUrl, function(resultList) {
                  me.displayResults(resultList, categoryId);
                  me.categoryCache[cacheKey] = resultList;
              });
          }
      }

      var productPicker = new ProductPicker('picker-tree', false, function(products) {
          alert('selected: ' + products);
      });

      $(document).ready(function() {
        $('#picker-tree a.tree-cat-url').each(function (i) {
          var classes = this.className.split(' ');
          for (var ii=0; ii<classes.length; ++ii) {
            if (0 == classes[ii].indexOf('c:')) {
              var token = classes[ii].split(':');
              if (0 < token[1]) {
                $(this).click(function() {
                  productPicker.categoryClick(token[1], 1);
                  return false;
                });
              }
              break;
            }
          }
        });
      });
  </script>

