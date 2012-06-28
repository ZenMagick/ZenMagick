<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2012 zenmagick.org
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

<?php $crumbtrail->addCategoryPath()->addManufacturer()->addProduct($currentProduct->getId())->addCrumb(_zm('Tell A Friend')) ?>
<?php echo $form->open('tell_a_friend', 'products_id=' . $request->getProductId(), true, array('id'=>'tellAFriend')) ?>
   <fieldset>
        <legend><?php _vzm("Tell a friend about '%s'", $currentProduct->getName()); ?></legend>

        <label for="fromName"><?php _vzm("Your Name") ?><span>*</span></label>
        <input type="text" id="fromName" name="fromName" size="40" value="<?php echo $html->encode($tellAFriend->getFromName()) ?>" /><br />

        <label for="fromEmail"><?php _vzm("Your Email") ?><span>*</span></label>
        <input type="text" id="fromEmail" name="fromEmail" size="40" value="<?php echo $html->encode($tellAFriend->getFromEmail()) ?>" /><br />

        <label for="toName"><?php _vzm("Friend's Name") ?><span>*</span></label>
        <input type="text" id="toName" name="toName" size="40" value="<?php echo $html->encode($tellAFriend->getToName()) ?>" /><br />

        <label for="toEmail"><?php _vzm("Friend's Email") ?><span>*</span></label>
        <input type="text" id="toEmail" name="toEmail" size="40" value="<?php echo $html->encode($tellAFriend->getToEmail()) ?>" /><br />

        <label for="message"><?php _vzm("Message") ?></label>
        <textarea id="message" name="message" cols="30" rows="7"><?php echo $html->encode($tellAFriend->getMessage()) ?></textarea>
        <p class="legend"><?php _vzm("<span>*</span> Mandatory fields") ?></p>
    </fieldset>
    <div class="btn"><input type="submit" class="btn" value="<?php _vzm("Send") ?>" /></div>
</form>
<div class="advisory">
    <strong><?php _vzm("The following message is included with all emails sent from this site:") ?></strong><br />
    <?php echo $utils->staticPageContent('email_advisory') ?>
</div>
