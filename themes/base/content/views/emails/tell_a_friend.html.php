<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2012 zenmagick.org
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
?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html lang="<?php echo $language->getCode() ?>">
<head>
<title><?php _vzm("Product recommendation from %s at %s", $emailMessage->getFromName(), $settingsService->get('storeName')) ?></title>
</head>
<body>
<body>
<div style="font-family:Verdana,Arial,Helvetica,sans-serif;font-size:10pt;">
<p><?php _vzm("Hi %s,", $emailMessage->getToName()) ?></p>

<p><?php _vzm("Your friend, %s, thought that you would be interested in %s from %s.", $emailMessage->getFromName(), $currentProduct->getName(), $settingsService->get('storeName')) ?></p>

<?php if ($emailMessage->hasMessage()) { ?>
<p>
<?php _vzm("%s also sent a note saying:", $emailMessage->getFromName()) ?><br>
<?php echo $html->text2html($emailMessage->getMessage()) ?>
</p>
<?php } ?>

<?php $href = '<a href="'.$net->product($currentProduct->getId(), null).'">'.$html->encode($currentProduct->getName()).'</a>'; ?>
<p><?php _vzm("To view the product, click on the following link or copy and paste the link into your web browser: %s", $href) ?></p>

<p><?php _vzm("Regards, %s", $settingsService->get('storeOwner')) ?></p>

<?php echo $utils->staticPageContent('email_advisory') ?>
</div>
</body>
</html>
