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

  <a href="#TB_inline?height=355&amp;width=600&amp;inlineId=productPicker&amp;modal=true" class="thickbox">Show hidden modal content.</a>

  <div id="productPicker" style="display:none;">
    <div style="float:left;width:35%;"><?php  echo zm_catalog_tree(ZMCategories::instance()->getCategoryTree(), '', false, false, 'picker-tree'); ?></div>
    <div id="picker-prod-list" style="border:1px solid black;margin:0 5px 0 35%;;width:60%;min-height:200px;">
    </div>
    <div style="clear:both;text-align:right;padding:5px;">
      <a class="btn" href="#" onclick="tb_remove();return false;">OK</a>
    </div>
  </div>
  <script type="text/javascript">
      $(document).ready(function() {
        $('#picker-tree a.tree-cat-url').each(function (i) {
          var classes = this.className.split(' ');
          for (var ii=0; ii<classes.length; ++ii) {
            if (0 == classes[ii].indexOf('c:')) {
              var token = classes[ii].split(':');
              if (0 < token[1]) {
                $(this).unbind().click(function() {
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
                              html += "Id: "+item.id+", Name: "+item.name  + '<br>';
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

