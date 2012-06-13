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

<div id="headerWrapper">
	<div id="logoWrapper" class="back">
    	<div id="logo"><div id="logoContent" class="unitPng"><a href="<?php echo $net->url('index') ?>" title="<?php echo _zm('ZenMagick - Smart e-commerce') ?>"><span>&nbsp;</span></a></div></div>
	</div>

	<!--bof header_content_wrapper-->
	<div id="headerContentWrapper" class="forward">
		<!--bof header_search-->
		<div class="searchHeader forward unitPng">
      <?php echo $form->open('search', '', $request->isSecure(), array('method' => 'get', 'name' => 'quick_find_header')) ?>
          <?php $onfocus = "if(this.value=='"._zm('Enter search keywords here')."') this.value='';" ?>
          <?php $onblur = "if(this.value=='') this.value='"._zm('Enter search keywords here')."';" ?>
          <input type="text" name="keywords" size="6" maxlength="30" style="width:180px" value="<?php echo $html->encode($request->query->get('keywords', _zm('Enter search keywords here'))) ?>" onfocus="<?php echo $onfocus ?>" onblur="<?php echo $onblur ?>" />
      </form>
		</div>
		<!--eof header_search-->
		<br class="clearBoth" />

		<div id="smallNaviHeader" class="forward">
			<ul>
			<?php if (!$request->isAnonymous()) { ?>
			  <?php if ($request->isRegistered()) { ?>
				  <li><a href="<?php echo $net->url('account') ?>" title="<?php _vzm("MyAccount") ?>"><span class="colorOrangeLink"><?php _vzm("MyAccount") ?></span></a></li>
				  <li>|</li>
        <?php } ?>
		    	<li><a href="<?php echo $net->url('logoff') ?>"><span class="colorBlackLink"><?php echo _zm('Log Out') ?></span></a></li>
			<?php } else { ?>
					<li><a href="<?php echo $net->url('create_account'); ?>"><span class="colorOrangeLink"><?php _vzm("Register"); ?></span></a></li>
					<li>|</li>
		    		<li><a href="<?php echo $net->url('login'); ?>"><span class="colorBlackLink"><?php _vzm("Login"); ?></span></a></li>
			<?php } ?>
			<?php if (!$request->getShoppingCart()->isEmpty() && !$request->isCheckout()) { ?>
				<li>|</li>
				<li><a href="<?php echo $net->url('checkout_shipping'); ?>"><span class="colorOrangeLink"><?php echo _zm('Checkout') ?></span></a></li>
				<li>|</li>
		    	<li class="shoppingCart unitPng">
					<a href="<?php echo $net->url('shopping_cart'); ?>"><span class="colorBlackLink"><?php echo _zm('Shopping Cart') ?></span></a>
					<span class="colorBlack">(<?php echo $request->getShoppingCart()->getSize(); ?>): </span>
					<span class="price colorOrange"><?php echo $utils->formatMoney($request->getShoppingCart()->getTotal()) ?></span>
				</li>
			<?php }?>
			</ul>
		</div>
		<br class="clearBoth" />
	</div>
	<!--eof header_content_wrapper-->
	<br class="clearBoth" />
	<!--eof-branding display-->
	<!--eof header_content_wrapper-->
</div>

<div id="navEZPagesTop" class="unitPng">
<ul>
	<li><a href="<?php echo $net->url('index') ?>"><span class="navEZCol"><?php _vzm("Home"); ?></span></a></li>
  <?php foreach ($container->get('ezPageService')->getPagesForHeader($session->getLanguageId()) as $page) { ?>
    <?php $text = '<span class="navEZCol">'.$html->encode($page->getTitle()).'</span>'; ?>
    <li><?php echo $html->ezpageLink($page->getId(), $text); ?></li>
  <?php } ?>
</ul>
</div>
