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
<?php $crumbtrail->addCrumb(_zm('Gift Certificate FAQ')) ?>
<h2><?php _vzm("Gift Certificate FAQ") ?></h2>

<?php /* the available FAQ entries */
    $faq_topics = array(
      'gv_purchase' => _zm('Purchasing Gift Certificates'),
      'gv_send' => _zm('How to send Gift Certificates'),
      'gv_use' => _zm('Buying with Gift Certificates'),
      'gv_redeem' => _zm('Redeeming Gift Certificates'),
      'gv_trouble' => _zm('When problems occur...')
    );
?>

<ul>
<?php foreach ($faq_topics as $key => $title) { ?>
    <li><a href="<?php echo $net->generate('gv_faq', array('topic' => $key)) ?>"><?php _vzm($title) ?></a></li>
<?php } ?>
</ul>

<?php if (null != ($topic = $request->query->get('topic')) && array_key_exists($topic, $faq_topics)) { ?>
    <?php echo $utils->staticPageContent($topic); ?>
<?php } ?>

<?php echo $form->open('gv_redeem', '', true, array('id'=>'gv_redeem')) ?>
  <fieldset>
    <legend><?php _vzm("Redemption code details") ?></legend>
    <div>
      <label for="gvCode"><?php _vzm("Redemption Code") ?></label>
      <input type="text" id="gvCode" name="couponCode" value="<?php echo $html->encode($gvRedeem->getCouponCode()) ?>" />
    </div>
  </fieldset>
  <div class="btn"><input type="submit" class="btn" value="<?php _vzm("Redeem") ?>" /></div>
</form>
