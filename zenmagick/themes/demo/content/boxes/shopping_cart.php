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

<?php $utils->jsBottom('jquery.js') ?>
<?php $utils->jsBottom('interface.js') ?>

<script type="text/javascript">
    // callback used by drop handler to update cart contents
    var updateSBCartContent = function(msg) {
        var href_template = '<?php $net->product('{productId}') ?>';
        // NOTE: using json.js here will break IE and create ugly JS erros in FF
        var cart = eval('(' + msg + ')');
        // clear
        $('#sb_cart').empty();

        for (var ii=0; ii < cart.items.length; ++ii) {
            var item = cart.items[ii];
            var href = href_template.replace(/{productId}/, item.id);
            $('#sb_cart')
              .append(item.qty + ' x ' + '<a href="' + href + '">' + item.name + '</a><br />');
        }
        // total
        $('#sb_cart').append('<hr/>')
          .append('<p><img id="cart_progress" src="<?php $zm_theme->themeUrl('images/circle-ball-dark-antialiased.gif') ?>" style="display:none;float:left;" alt="progress" />' + cart.total + '</p>');


        return msg;
    };
</script>

<?php if (!ZMRequest::isCheckout()) { ?>
<h3><a href="<?php $net->url(FILENAME_SHOPPING_CART, '', true) ?>"><?php zm_l10n("[More]") ?></a><?php zm_l10n("Shopping Cart") ?></h3>
    <div id="sb_cart" class="box">
        <?php if (ZMRequest::getShoppingCart()->isEmpty()) { ?>
            <?php zm_l10n("Cart is Empty") ?>
        <?php } ?>
        <?php foreach (ZMRequest::getShoppingCart()->getItems() as $item) { ?>
            <?php echo $item->getQty(); ?> x <a href="<?php $net->product($item->getId()) ?>"><?php $html->encode($item->getName()); ?></a><br />
        <?php } ?>
        <hr/>
        <p><img id="cart_progress" src="<?php $zm_theme->themeUrl('images/circle-ball-dark-antialiased.gif') ?>" style="display:none;float:left;" alt="progress" /><?php $utils->formatMoney(ZMRequest::getShoppingCart()->getTotal()) ?></p>
    </div>
<?php } ?>
