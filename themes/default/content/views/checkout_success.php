<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2011 zenmagick.org
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

<?php echo $form->open('checkout_success', 'action=update', true, array('onsubmit'=>null)) ?>
    <h2><?php _vzm("Thanks for shopping with us") ?></h2>
    <p><?php _vzm("Your order number is: <strong>%s</strong>", $currentOrder->getId()) ?></p>
    <?php if ($request->getAccount()->isRegistered()) { ?>
        <?php $account = '<a href="' . $net->url('account') . '">' . _zm("My Account") . '</a>'; ?>
        <p><?php _vzm("You can view your full order history by going to the %s page and by clicking on view all orders.", $account) ?></p>
    <?php } ?>
    <?php $customercare = '<a href="' . $net->url('contact_us') . '">' . _zm("Customer Service") . '</a>'; ?>
    <p><?php _vzm("Please direct any questions you have to %s.", $customercare) ?></p>

    <?php if (!$request->getAccount()->isGlobalProductSubscriber()) { ?>
        <fieldset>
            <legend><?php _vzm("Product Notifications") ?></legend>
            <h4><?php _vzm("Please notify me of updates to the products I have selected below:") ?></h4>
            <?php foreach ($currentOrder->getOrderItems() as $orderItem) { $id = "not_" . $orderItem->getProductId(); ?>
                <p>
                    <input type="checkbox" id="<?php echo $id ?>" name="notify[]" value="<?php echo $orderItem->getProductId() ?>" />
                    <label for="<?php echo $id ?>"><?php echo $html->encode($orderItem->getName()) ?></label><br />
                </p>
            <?php } ?>
            <div class="btn"><input type="submit" class="btn" value="<?php _vzm("Update") ?>" /></div>
        </fieldset>
    <?php } ?>

    <?php if (null != ($downloads = $currentOrder->getDownloads()) && 0 < count($downloads)) { ?>
        <fieldset>
            <legend><?php _vzm('Downloads') ?></legend>
            <p><?php _vzm('To download your files click the download button and choose "Save to Disk" from the popup menu.') ?></p>
            <table class="grid">
              <tr>
                  <th><?php _vzm('Item') ?></th>
                  <th><?php _vzm('Filename') ?></th>
                  <th><?php _vzm('Size') ?></th>
                  <th><?php _vzm('Remaining') ?></th>
                  <th></th>
              </tr>
              <?php foreach ($downloads as $download) { $downloadProduct = ZMProducts::instance()->getProductForId($download->getProductId(), $session->getLanguageId()); ?>
                  <tr>
                      <th><?php echo $html->encode($downloadProduct->getName()) ?></th>
                      <th><?php echo $html->encode($download->getFilename()) ?></th>
                      <th><?php echo $download->getFileSize() ?> bytes</th>
                      <th><?php echo $download->getDownloadCount() ?></th>
                      <th><a href="<?php echo $net->url('download', 'order='.$currentOrder->getId().'&id='.$download->getId(), $request->isSecure()) ?>"><?php _vzm('Download') ?></a></th>
                  </tr>
              <?php } ?>
            </table>
        </fieldset>
    <?php } ?>

    <?php $voucherBalance = $request->getAccount()->getVoucherBalance(); ?>
    <?php if (0 < $voucherBalance) { ?>
        <fieldset>
            <legend><?php _vzm("Gift Certificate Account") ?></legend>
            <p>
                <?php _vzm("You have funds (%s) in your Gift Certificate Account.", $utils->formatMoney($voucherBalance)) ?><br />
                <?php $email = '<a href="' . $net->url('gv_send') . '">' . _zm("email") . '</a>'; ?>
                <?php _vzm("If you want to, you can send those funds by %s to someone.", $email) ?>
            </p>
            <div class="btn"><a href="<?php echo $net->url('gv_send') ?>" class="btn"><?php _vzm("Send Gift Certificate") ?></a></div>
        </fieldset>
    <?php } ?>

    <?php if ($request->isGuest()) { ?>
        <fieldset>
            <legend><?php _vzm("Order Status Check") ?></legend>
            <p>
                <?php $lookupLink = '<a href="' . $net->url('guest_history') . '">' . _zm("order status check") . '</a>'; ?>
                <?php _vzm("You can check the status of your order using the %s.", $lookupLink) ?>
            </p>
        </fieldset>
    <?php } ?>
</form>
