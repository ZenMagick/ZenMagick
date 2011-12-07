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
<?php _vzm("Dear %s %s,", $currentAccount->getFirstName(), $currentAccount->getLastName()) ?>


<?php _vzm('You recently purchased a Gift Certificate from our online store at %s', $settings->get('storeName')) ?>


<?php _vzm('For security reasons this was not made immediately available to you. However, this amount has now been released. You may now visit our store and send the value of the Gift Certificate via email to someone else, or use it yourself.') ?>


<?php _vzm('The Gift Certificate(s) you purchased are worth %s', $utils->formatMoney($couponQueue->getAmount(), false)) ?>


<?php _vzm('Thank you for shopping with us!') ?>

<?php _vzm("Sincerely, %s", $settings->get('storeOwner')) ?>


<?php echo strip_tags($utils->staticPageContent('email_advisory')) ?>
<?php echo $office_only_text; ?>
