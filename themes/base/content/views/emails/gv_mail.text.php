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
 */ if (empty($message)) { ?>
<?php _vzm("We're pleased to offer you a Gift Certificate") ?>
<?php } else { ?>
<?php echo $message; ?>
<?php } ?>


<?php _vzm('The Gift Certificate is worth %s', $utils->formatMoney($currentCoupon->getAmount(), false)) ?>


<?php _vzm("The code to redeem your Gift Certificate is: %s", $currentCoupon->getCode()) ?>


<?php _vzm("To redeem your gift, visit %s", $request->absoluteUrl($net->url('gv_redeem', 'couponCode='.$currentCoupon->getCode(), true), true, true)) ?>


<?php echo strip_tags($utils->staticPageContent('email_advisory')) ?>
<?php echo $office_only_text; ?>
