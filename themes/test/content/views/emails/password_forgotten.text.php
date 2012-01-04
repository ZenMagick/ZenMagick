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
?><?php _vzm("*** TEST *** New Password Request\n") ?>

<?php _vzm("This is in response to a request for a new password for your account at %s.\n", $settingsService->get('storeName')) ?>
<?php _vzm("Your new password is: %s\n\n", $password) ?>
<?php _vzm("For security reasons please remember to change your password next time you logon.\n") ?>

<?php _vzm("Regards, %s\n", $settingsService->get('storeName')) ?>

<?php echo strip_tags($utils->staticPageContent('email_advisory')) ?>
