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
<?php $view->extend('StorefrontBundle::default_layout.html.php'); ?>
<?php $crumbtrail->addCrumb(_zm('Contact Us')) ?>
<h3><?php _vzm("Store Contact Details") ?></h3>
<p><address><?php echo nl2br($settingsService->get('storeNameAddress')); ?></address></p>
<br/>

<?php echo $utils->staticPageContent("contact_us") ?>

<?php echo $form->open('contact_us', 'action=send', false, array('id' => 'contactUs')) ?>
    <fieldset>
        <legend><?php _vzm("Contact us") ?></legend>
        <label for="name"><?php _vzm("Full Name") ?><span>*</span></label>
        <input type="text" id="name" name="name" size="40" value="<?php echo $html->encode($contactUs->getName()) ?>" /><br />

        <label for="email"><?php _vzm("Email Address") ?><span>*</span></label>
        <input type="text" id="email" name="email" size="40" value="<?php echo $html->encode($contactUs->getEmail()) ?>" /><br />

        <label for="message"><?php _vzm("Message") ?><span>*</span></label>
        <textarea id="message" name="message" cols="30" rows="7"><?php echo $html->encode($contactUs->getMessage()) ?></textarea>
        <p class="legend"><?php _vzm("<span>*</span> Mandatory fields") ?></p>
    </fieldset>
    <div class="btn"><input type="submit" class="btn" value="<?php _vzm("Send") ?>" /></div>
</form>
