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

<?php $email = '<a href="' . $view['router']->generate('contact_us') . '">' . _zm("store owner") . '</a>'; ?>
<?php $contactus = '<a href="' . $view['router']->generate('contact_us') . '">' . _zm("contact us") . '</a>'; ?>
<h3><?php _vzm("Congratulations!" ) ?></h3>
<p><?php _vzm("Your new account has been successfully created! You can now take advantage of member privileges to enhance your online shopping experience with us.") ?></p>
<p><?php _vzm("If you have <small><strong>ANY</strong></small> questions about the operation of this online shop, please email the %s.", $email) ?></p>
<p><?php _vzm("A confirmation has been sent to the provided email address. If you have not received it within the hour, please %s.", $contactus) ?></p>
