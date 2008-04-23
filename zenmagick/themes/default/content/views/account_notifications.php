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

<p><?php zm_l10n("The product notification list allows you to stay up to date on products you find of interest.") ?></p>
<p><?php zm_l10n("To be up to date on all product changes, select <strong>Global Product Notifications</strong>.") ?></p>
<?php zm_secure_form(FILENAME_ACCOUNT_NOTIFICATIONS, "action=process") ?>
    <fieldset>
        <legend><?php zm_l10n("Global Product Notifications") ?></legend>
        <p><input type="checkbox" id="product_global" name="product_global" value="1"<?php $form->checked($zm_account->isGlobalProductSubscriber(), true) ?> /><label for="product_global"><?php zm_l10n("Global Product Notification") ?></label></p>
    </fieldset>

    <?php if (!$zm_account->isGlobalProductSubscriber() && $zm_account->hasProductSubscriptions()) { ?>
        <fieldset>
            <legend><?php zm_l10n("Product Notifications") ?></legend>
            <?php $ii=0; 
            foreach ($zm_account->getSubscribedProducts() as $productId) { $product = ZMProducts::instance()->getProductForId($productId); ?>
                <p><input type="checkbox" id="products_<?php echo $ii ?>" name="notify[<?php echo $ii ?>]" value="<?php echo $productId ?>" checked="checked" /><label for="products_<?php echo $ii ?>"><?php echo null != $product ? $html->encode($product->getName(), false) : '???' ?></label></p>
            <?php ++$ii; } ?>
        </fieldset>
    <?php } ?>
    <div class="btn"><input type="submit" class="btn" value="<?php zm_l10n("Update") ?>" /></div>
</form>
