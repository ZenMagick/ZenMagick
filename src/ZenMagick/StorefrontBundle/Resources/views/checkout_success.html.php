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
<?php $view->extend('StorefrontBundle::default_layout.html.php'); ?>
<?php $view['slots']->set('crumbtrail', $crumbtrail->addCrumb(_zm('Order Confirmation'))); ?>
<?php echo $form->open('checkout_success', 'action=update', true, array('onsubmit'=>null)) ?>
    <h2><?php _vzm("Thanks for shopping with us") ?></h2>
    <p><?php _vzm("Your order number is: <strong>%s</strong>", $currentOrder->getId()) ?></p>
    <?php if ($view['security']->isGranted('ROLE_REGISTERED')) { ?>
        <?php $account = '<a href="' . $view['router']->generate('account') . '">' . _zm("My Account") . '</a>'; ?>
        <p><?php _vzm("You can view your full order history by going to the %s page and by clicking on view all orders.", $account) ?></p>
    <?php } ?>
    <?php $customercare = '<a href="' . $view['router']->generate('contact_us') . '">' . _zm("Customer Service") . '</a>'; ?>
    <p><?php _vzm("Please direct any questions you have to %s.", $customercare) ?></p>

    <?php if (!empty($productsToSubscribe)) { ?>
        <fieldset>
            <legend><?php _vzm("Product Notifications") ?></legend>
            <h4><?php _vzm("Please notify me of updates to the products I have selected below:") ?></h4>
            <?php foreach ($productsToSubscribe as $productId => $productName) { $id = "not_" . $productId; ?>
                <p>
                    <input type="checkbox" id="<?php echo $id ?>" name="notify[]" value="<?php echo $productId ?>" />
                    <label for="<?php echo $id ?>"><?php echo $view->escape($productName) ?></label><br />
                </p>
            <?php } ?>
            <div class="btn"><input type="submit" class="btn" value="<?php _vzm("Update") ?>" /></div>
        </fieldset>
    <?php } ?>

    <?php if (null != ($downloads = $currentOrder->getDownloads()) && 0 < count($downloads)) { ?>
        <fieldset>
            <legend><?php _vzm('Downloads') ?></legend>
            <p><?php _vzm("To download your files click the download button and choose \"Save to Disk\" from the popup menu.") ?></p>
            <table class="grid">
              <tr>
                  <th><?php _vzm('Item') ?></th>
                  <th><?php _vzm('Filename') ?></th>
                  <th><?php _vzm('Size') ?></th>
                  <th><?php _vzm('Remaining') ?></th>
                  <th></th>
              </tr>
              <?php foreach ($downloads as $download) { $downloadProduct = $view->container->get('productService')->getProductForId($download->getProductId(), $view['request']->getLocaleId()); ?>
                  <tr>
                      <th><?php echo $view->escape($downloadProduct->getName()) ?></th>
                      <th><?php echo $view->escape($download->getFilename()) ?></th>
                      <th><?php echo $download->getFileSize() ?> bytes</th>
                      <th><?php echo $download->getDownloadCount() ?></th>
                      <th><a href="<?php echo $view['router']->generate('download', array('order' => $currentOrder->getId(), 'id' => $download->getId())) ?>"><?php _vzm('Download') ?></a></th>
                  </tr>
              <?php } ?>
            </table>
        </fieldset>
    <?php } ?>

    <?php $voucherBalance = $app->getUser()->getVoucherBalance(); ?>
    <?php if (0 < $voucherBalance) { ?>
        <fieldset>
            <legend><?php _vzm("Gift Certificate Account") ?></legend>
            <p>
                <?php _vzm("You have funds (%s) in your Gift Certificate Account.", $utils->formatMoney($voucherBalance)) ?><br />
                <?php $email = '<a href="' . $view['router']->generate('gv_send') . '">' . _zm("email") . '</a>'; ?>
                <?php _vzm("If you want to, you can send those funds by %s to someone.", $email) ?>
            </p>
            <div class="btn"><a href="<?php echo $view['router']->generate('gv_send') ?>" class="btn"><?php _vzm("Send Gift Certificate") ?></a></div>
        </fieldset>
    <?php } ?>

    <?php if (!$view['security']->isGranted('ROLE_REGISTERED')) { ?>
        <fieldset>
            <legend><?php _vzm("Order Status Check") ?></legend>
            <p>
                <?php $lookupLink = '<a href="' . $view['router']->generate('guest_history') . '">' . _zm("order status check") . '</a>'; ?>
                <?php _vzm("You can check the status of your order using the %s.", $lookupLink) ?>
            </p>
        </fieldset>
    <?php } ?>
</form>