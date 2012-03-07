<?php
/*
 * ZenMagick - Extensions for zen-cart
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
?>

<address><?php echo nl2br($settingsService->get('storeNameAddress')); ?></address>
<div id="contactUsNoticeContent" class="content">
	<?php echo $utils->staticPageContent("contact_us") ?>
</div>

<?php echo $form->open('contact_us', 'action=send', false, array('id' => 'contactUs')) ?>
    <fieldset>
        <legend><?php _vzm("Contact us") ?></legend>
        <table cellspacing="0" cellpadding="0">
	        <tr>
	        	<td class="label"><?php _vzm("Full Name") ?><span>*</span></td>
        		<td><input type="text" id="name" name="name" size="40" value="<?php echo $html->encode($contactUs->getName()) ?>" /></td>
        	</tr>
        	<tr>
        		<td><?php _vzm("Email Address") ?><span>*</span></td>
        		<td><input type="text" id="email" name="email" size="40" value="<?php echo $html->encode($contactUs->getEmail()) ?>" /></td>
        	</tr>
			<tr>
				<td><?php _vzm("Message") ?><span>*</span></td>
        		<td><textarea id="message" name="message" cols="30" rows="7"><?php echo $html->encode($contactUs->getMessage()) ?></textarea></td>
      		</tr>
        </table>
    </fieldset>
    <div class="btnwrapper"><input type="submit" class="btn" value="<?php _vzm("Send") ?>" /></div>
</form>
