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
?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html lang="<?php echo $language->getCode() ?>">
<head>
<title><?php _vzm("A gift from %s", $currentAccount->getFullName()) ?></title>
</head>
<body>
<body>
<div style="font-family:Verdana,Arial,Helvetica,sans-serif;font-size:10pt;">
<p><?php _vzm("Dear %s,", $gvReceiver->getName()) ?></p>

<p><?php _vzm('You have been sent a Gift Certificate worth %s by %s.', $utils->formatMoney($gvReceiver->getAmount(), false), $currentAccount->getFullName()) ?></p>
<p><?php _vzm("The code to redeem your Gift Certificate is: %s.", $currentCoupon->getCode()) ?></p>
<?php if ($gvReceiver->hasMessage()) { ?>
<p>
<?php _vzm("%s says:", $currentAccount->getFirstName()); ?><br>
<?php echo $html->text2html($gvReceiver->getMessage()) ?>
</p>
<?php } ?>
<?php $href = '<a href="'.$net->absoluteUrl($net->url('gv_redeem', 'couponCode='.$currentCoupon->getCode(), true), true, true).'">'.$settingsService->get('storeName').'</a>'; ?>
<p><?php _vzm("To redeem your gift, visit %s.", $href) ?></p>

<?php if (!isset($isSupressDisclaimer)) { echo $utils->staticPageContent('email_advisory'); } ?>
<?php echo $office_only_html; ?>
</div>
</body>
</html>
