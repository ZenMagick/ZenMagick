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
?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html lang="<?php echo $language->getCode() ?>">
<head>
<title><?php _vzm("A Gift Certificate from %s", $settings->get('storeName')) ?></title>
</head>
<body>
<body>
<div style="font-family:Verdana,Arial,Helvetica,sans-serif;font-size:10pt;">
<?php if (empty($htmlMessage)) { ?>
<p><?php _vzm("We're pleased to offer you a Gift Certificate") ?></p>
<?php } else { ?>
<div><?php echo $message; ?></div>
<?php } ?>

<p><?php _vzm('You have been sent a Gift Certificate worth %s.', $utils->formatMoney($currentCoupon->getAmount(), false)) ?></p>
<p><?php _vzm("The code to redeem your Gift Certificate is: %s.", $currentCoupon->getCode()) ?></p>
<?php $href = '<a href="'.$net->url('gv_redeem', 'couponCode='.$currentCoupon->getCode(), true).'">'.$settings->get('storeName').'</a>'; ?>
<p><?php _vzm("To redeem your gift, visit %s.", $href) ?></p>

<?php if (!isset($isSupressDisclaimer)) { echo $utils->staticPageContent('email_advisory'); } ?>
<?php echo $office_only_html; ?>
</div>
</body>
</html>
