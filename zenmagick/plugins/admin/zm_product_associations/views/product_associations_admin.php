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
    <?php $types = ProductAssociationService::instance()->getAssociationTypes(); ?>
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
      <div id="picker-prod-list" style="border:1px solid black;margin:5px;height:320px;">
      </div>
      <div id="picker-selected">
      </div>
      <div style="text-align:right;padding:5px;">
        <a class="btn" href="#" onclick="picker_close();return false;">OK</a>
      </div>
    </div>
  </div>
  <script type="text/javascript">
      // XXX: wrap in class
      var select_single = true;

      function picker_close() {
        tb_remove();
        $('#picker-prod-list').html('');
      }

      function picker_picked(id) {
        alert(id);
        if (select_single) {
          //TODO: handle selection
          picker_close();
        }
      }

      $(document).ready(function() {
        $('#picker-tree a.tree-cat-url').each(function (i) {
          var classes = this.className.split(' ');
          for (var ii=0; ii<classes.length; ++ii) {
            if (0 == classes[ii].indexOf('c:')) {
              var token = classes[ii].split(':');
              if (0 < token[1]) {
                $(this).click(function() {
                  var url = '<?php $toolbox->net->ajax('catalog', 'getProductsForCategoryId') ?>'+'&categoryId='+token[1];
                  prodList = $('#picker-prod-list');
                  prodList.html('Loading...');
                  $.ajax({
                      type: 'GET',
                      url: '<?php $toolbox->net->ajax('catalog', 'getProductsForCategoryId') ?>'+'&categoryId='+token[1],
                      success: function(msg) {
                          prodList.html('');
                          //XXX: fix
                          var json = eval('(' + msg + ')');
                          var html = '';
                          for (var jj=0; jj < json.length; ++jj) {
                              var item = json[jj];
                              html += '<a href="#" onclick="picker_picked('+item.id+')">'+item.name+'</a><br>';
                          }
                          prodList.html(html);
                      }
                  });
                  return false;
                });
              }
              break;
            }
          }
        });
      });
  </script>

