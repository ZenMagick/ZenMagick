<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2012 zenmagick.org
 *
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

<?php $crumbtrail->addCrumb(_zm('Account'), $net->url('account', '', true))->addCrumb(_zm('Product Notifications')) ?>
<p><?php _vzm("The product notification list allows you to stay up to date on products you find of interest.") ?></p>
<p><?php _vzm("To be up to date on all product changes, select <strong>Global Product Notifications</strong>.") ?></p>
<?php echo $form->open('account_notifications', '', true, array('onsubmit'=>null)) ?>
    <fieldset>
        <legend><?php _vzm("Global Product Notifications") ?></legend>
        <p><input type="checkbox" id="product_global" name="product_global" value="1"<?php $form->checked(true, $currentAccount->isGlobalProductSubscriber(), true) ?> /><label for="product_global"><?php _vzm("Global Product Notification") ?></label></p>
    </fieldset>

    <?php if (!$currentAccount->isGlobalProductSubscriber() && $currentAccount->hasProductSubscriptions()) { ?>
        <fieldset>
            <legend><?php _vzm("Product Notifications") ?></legend>
            <?php $ii=0;
            foreach ($currentAccount->getSubscribedProducts() as $productId) { $product = $container->get('productService')->getProductForId($productId, $session->getLanguageId()); ?>
                <p><input type="checkbox" id="products_<?php echo $ii ?>" name="notify[<?php echo $ii ?>]" value="<?php echo $productId ?>" checked="checked" /><label for="products_<?php echo $ii ?>"><?php echo null != $product ? $html->encode($product->getName()) : '???' ?></label></p>
            <?php ++$ii; } ?>
        </fieldset>
    <?php } ?>
    <div class="btn"><input type="submit" class="btn" value="<?php _vzm("Update") ?>" /></div>
</form>
