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
 */ _vzm("Dear %s,", $gvReceiver->getName()) ?>


<?php _vzm('You have been sent a Gift Certificate worth %s by %s.', $utils->formatMoney($gvReceiver->getAmount(), false), $currentAccount->getFullName()) ?>

<?php _vzm("The code to redeem your Gift Certificate is: %s", $currentCoupon->getCode()) ?>

<?php if ($gvReceiver->hasMessage()) { ?>
<?php _vzm("%s says:", $currentAccount->getFirstName()); ?>

<?php echo $gvReceiver->getMessage() ?>

<?php } ?>

<?php _vzm("To redeem your gift, visit %s", $net->absoluteUrl($net->url('gv_redeem', 'couponCode='.$currentCoupon->getCode(), true), true, true)) ?>


<?php echo strip_tags($utils->staticPageContent('email_advisory')) ?>
<?php echo $office_only_text; ?>
