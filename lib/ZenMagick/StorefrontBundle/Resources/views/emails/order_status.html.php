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
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html lang="<?php echo $language->getCode() ?>">
<head>
<title><?php _vzm("Order update #%s", $currentOrder->getId()) ?></title>
</head>
<body>
<body>
<div style="font-family:Verdana,Arial,Helvetica,sans-serif;font-size:10pt;">
<p><?php _vzm("Dear %s %s,", $currentAccount->getFirstName(), $currentAccount->getLastName()) ?></p>

<p><?php _vzm("This is to inform you that your order #%s has been updated.", $currentOrder->getId()) ?></p>
<?php if (ZenMagick\StoreBundle\Entity\Account::REGISTERED == $currentAccount->getType()) {
    $href = '<a href="'.$net->absoluteUrl($net->url('account_history_info', 'order_id='.$currentOrder->getId()), true, true).'">'.sprintf(_zm("order #%s"), $currentOrder->getId()).'</a>';
} else {
    $href = '<a href="'.$net->absoluteUrl($net->url('guest_history'), true, true).'">'.sprintf(_zm("order #%s"), $currentOrder->getId()).'</a>';
} ?>
<p><?php _vzm("More details can be found at the following URL: %s", $href) ?></p>

<?php if ($newOrderStatus != $currentOrder->getStatusName()) { ?>
<?php _vzm("The new order status is: %s.", $newOrderStatus) ?>
<?php } ?>

<?php if (!empty($comment)) { ?>
<p><?php _vzm("The following comment has been added to your order:") ?></p>
<p><?php echo $html->text2html($comment) ?></p>
<?php } ?>

<p><?php _vzm("Regards, %s", $settingsService->get('storeName')) ?></p>

<?php echo $utils->staticPageContent('email_advisory') ?>
</div>
</body>
</html>
