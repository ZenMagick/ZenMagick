<?php
/*
 * ZenMagick - Extensions for zen-cart
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
 *
 * $Id$
 */
?>

<?php $utils->jsNow('jquery.js') ?>
<?php $utils->jsBottom('interface.js') ?>

<script type="text/javascript">
    // set up drag/drop
    $(document).ready(function() {
        $('a.product').Draggable({revert:true, fx:300, ghosting:true, opacity:0.4});
        $('#sb_cart').Droppable({
            accept: 'product', 
            activeclass: 'activeCart', 
            hoverclass: 'hoverCart',
            tolerance: 'intersect',
            onActivate: function(dragged) {
              if (!this.shakedFirstTime) {
                $(this).Shake(3);
                this.shakedFirstTime = true;
              }
            },
            onDrop: addProductToCart
        });
    });

    // add to cart action
    var addProductToCart = function(dragged) {
    // product_[id]
    var productId = $(dragged).attr('id');
    var loff = productId.lastIndexOf('_');
    var productId = parseInt(productId.substring(loff+1));
    if ('NaN' != productId) {
        $('#cart_progress').show();
        $.ajax({
            type: "POST",
            url: "<?php $net->ajax('shopping_cart', 'addProduct') ?>",
            data: "productId="+productId+"&quantity=1",
            success: function(msg) {
                // declared in sidebox, so easier to change layout...
                updateSBCartContent(msg);
            }
        });
    }
  };
</script>

<h2>Drag products into your shopping cart</h2>
<?php if ($zm_resultList->hasResults()) { ?>
    <div class="rnblk">
        <?php echo $this->fetch('views/resultlist/nav.php') ?>
        <?php echo $this->fetch('views/resultlist/options.php') ?>
    </div>

    <?php $form->open('compare_products', '', false, array('method' => 'get')) ?>
        <div class="rlist">
            <table cellspacing="0" cellpadding="0"><tbody>
                <?php $first = true; $odd = true; foreach ($zm_resultList->getResults() as $product) { ?>
                  <?php echo $this->fetch('views/resultlist/product.php') ?>
                <?php $first = false; $odd = !$odd; } ?>
            </tbody></table>
        </div>
        <div class="rnblk">
            <?php echo $this->fetch('views/resultlist/compare.php') ?>
            <?php echo $this->fetch('views/resultlist/nav.php') ?>
        </div>
    </form>
<?php } else { ?>
    <h2><?php zm_l10n("There are no products in this category") ?></h2>
<?php } ?>
