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

<?php if (isset($currentProduct)) { ?>
     <?php
      $isSubscribed = false;
      if ($session->isRegistered()) {
          $account = $app->getUser();
          if (null != $account) {
              $subscribedProducts = $account->getSubscribedProducts();
              $isSubscribed = in_array($currentProduct->getId(), $subscribedProducts);
          }
      }
    ?>
    <?php if ($session->isAnonymous() || !$isSubscribed) { ?>
        <h3><?php _vzm("Notifications") ?></h3>
        <div id="sb_product_notifications" class="box">
            <?php echo $form->open('account_notifications', '', true, array('onsubmit'=>null)) ?>
            <input type="hidden" name="notify_type" value="add" />
            <input type="hidden" name="notify[]" value="<?php echo $currentProduct->getId() ?>" />
            <input type="image" src="<?php echo $this->asUrl("images/big_tick.gif") ?>" alt="<?php _vzm("Notify me of updates to this product") ?>" title="<?php _vzm("Notify me of updates to this product") ?>" />
            <br />
            <?php _vzm("Notify me of updates to <strong>%s</strong>", $currentProduct->getName())?>
            </form>
        </div>
    <?php } else if ($isSubscribed) { ?>
        <h3><?php _vzm("Notifications") ?></h3>
        <div id="sb_product_notifications" class="box">
            <?php echo $form->open('account_notifications', '', true, array('onsubmit'=>null)) ?>
            <input type="hidden" name="notify_type" value="remove" />
            <input type="hidden" name="notify[]" value="<?php echo $currentProduct->getId() ?>" />
            <input type="image" src="<?php echo $this->asUrl("images/big_remove.gif") ?>" alt="<?php _vzm("Do not notify me of updates to this product") ?>" title="<?php _vzm("Do not notify me of updates to this product") ?>" />
            <br />
            <?php _vzm("Do not notify me of updates to <strong>%s</strong>", $currentProduct->getName())?>
            </form>
        </div>
    <?php } ?>
<?php } ?>
