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
 */
?>
<p><?php _vzm("Including store news, new products, special offers, and other promotional announcements.") ?></p>
<?php echo $form->open('account_newsletters', '', true, array('onsubmit'=>null)) ?>
    <fieldset>
        <legend><?php _vzm("Store Newsletter") ?></legend>
        <p><input type="checkbox" id="newsletter_general" name="newsletter_general" value="1"<?php $form->checked(true, $currentAccount->isNewsletterSubscriber(), true) ?> /><label for="newsletter_general"><?php _vzm("Receive Store Newsletter") ?></label></p>
    </fieldset>
    <div class="btn"><input type="submit" class="btn" value="<?php _vzm("Update") ?>" /></div>
</form>
