<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2010 zenmagick.org
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
 *
 * $Id$
 */
?>

<h3><?php zm_l10n("Store Contact Details") ?></h3>
<p><address><?php echo nl2br(ZMSettings::get('storeNameAddress')); ?></address></p>
<br/>

<?php echo $utils->staticPageContent("contact_us") ?>

<?php echo $form->open(FILENAME_CONTACT_US, 'action=send', false, array('id' => 'contactUs')) ?>
    <fieldset>
        <legend><?php zm_l10n("Contact us") ?></legend>
        <label for="name"><?php zm_l10n("Full Name") ?><span>*</span></label>
        <input type="text" id="name" name="name" size="40" value="<?php echo $html->encode($contactUs->getName()) ?>" /><br />

        <label for="email"><?php zm_l10n("Email Address") ?><span>*</span></label>
        <input type="text" id="email" name="email" size="40" value="<?php echo $html->encode($contactUs->getEmail()) ?>" /><br />

        <label for="message"><?php zm_l10n("Message") ?><span>*</span></label>
        <textarea id="message" name="message" cols="30" rows="7"><?php echo $html->encode($contactUs->getMessage()) ?></textarea>
        <p class="legend"><?php zm_l10n("<span>*</span> Mandatory fields") ?></p>

        <?php if (is_object($recaptcha)) { ?>
            <p><?php $recaptcha->showCaptcha(); ?></p>
        <?php } ?>

        <?php if (is_object($captcha)) { ?>
            <?php $captcha->showImage(); ?>
            <a href="<?php echo $net->url(null) ?>"><?php zm_l10n("Click to refresh page")?></a><br />
            <label for="captcha"><?php zm_l10n("Captcha") ?><span>*</span></label>
            <input type="text" id="captcha" name="captcha" value="" /><br />
        <?php } ?>

        <?php if (is_object($recaptcha)) { ?>
            <?php $recaptcha->showCaptcha(); ?>
        <?php } ?>
    </fieldset>
    <div class="btn"><input type="submit" class="btn" value="<?php zm_l10n("Send") ?>" /></div>
</form>
