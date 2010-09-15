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

<div id="headerWrapper">
	<div id="logoWrapper" class="back">
    	<div id="logo"><div id="logoContent" class="unitPng"><a href="<?php echo $net->url(FILENAME_DEFAULT) ?>" title="<?php echo HEADER_ALT_TEXT;?>"><span>&nbsp;</span></a></div></div>
	</div>
	
	<!--bof header_content_wrapper-->
	<div id="headerContentWrapper" class="forward">
		<?php 
			$content = "";
		  	$content .= zen_draw_form('quick_find_header', zen_href_link(FILENAME_ADVANCED_SEARCH_RESULT, '', 'NONSSL', false), 'get');
		  	$content .= zen_draw_hidden_field('main_page',FILENAME_ADVANCED_SEARCH_RESULT);
		  	$content .= zen_draw_hidden_field('search_in_description', '1') . zen_hide_session_id();
		    $content .= zen_draw_input_field('keyword', '', 'size="6" maxlength="30" style="width: 180px" value="' . HEADER_SEARCH_DEFAULT_TEXT . '" onfocus="if (this.value == \'' . HEADER_SEARCH_DEFAULT_TEXT . '\') this.value = \'\';" onblur="if (this.value == \'\') this.value = \'' . HEADER_SEARCH_DEFAULT_TEXT . '\';"');
		  	$content .= "</form>";
		?>
		<!--bof header_search-->
		<div class="searchHeader forward unitPng">
			<?php echo $content; ?>
		</div>
		<!--eof header_search-->
		<br class="clearBoth" />
		
		<div id="smallNaviHeader" class="forward">
			<ul>
			<?php if ($_SESSION['customer_id']) { ?>
				<li><a href="<?php echo $net->url(FILENAME_ACCOUNT);?>" title="<?php echo zm_l10n("MyAccount"); ?>"><span class="colorOrangeLink"><?php echo HEADER_TITLE_MY_ACCOUNT; ?></span></a></li>
				<li>|</li>
		    	<li><a href="<?php echo $net->url(FILENAME_LOGOFF); ?>"><span class="colorBlackLink"><?php echo HEADER_TITLE_LOGOFF; ?></span></a></li>
			<?php
				} else {
		        	if (STORE_STATUS == '0') {
			?>
					<li><a href="<?php echo $net->url(FILENAME_CREATE_ACCOUNT); ?>"><span class="colorOrangeLink"><?php echo zm_l10n("Register"); ?></span></a></li>
					<li>|</li>
		    		<li><a href="<?php echo $net->url(FILENAME_LOGIN); ?>"><span class="colorBlackLink"><?php echo zm_l10n("Login"); ?></span></a></li>
			<?php }} ?>
			<?php if (!$request->getShoppingCart()->isEmpty() && !$request->isCheckout()) { ?>
				<li>|</li>
				<li><a href="<?php echo $net->url(FILENAME_CHECKOUT_SHIPPING); ?>"><span class="colorOrangeLink"><?php echo HEADER_TITLE_CHECKOUT; ?></span></a></li>
				<li>|</li>
		    	<li class="shoppingCart unitPng">
					<a href="<?php echo $net->url(FILENAME_SHOPPING_CART); ?>"><span class="colorBlackLink"><?php echo HEADER_TITLE_CART_CONTENTS; ?></span></a>
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
<?php ZMLoader::make('ToolboxHtml'); ?>
	<li><a href="<?php echo HTTP_SERVER.DIR_WS_CATALOG;?>"><span class="navEZCol"><?php echo zm_l10n("Home"); ?></span></a></li>
<?php foreach (ZMEZPages::instance()->getPagesForHeader($session->getLanguageId()) as $page) { ?>
	<li><?php echo $html->ezpageLink($page->getId()); ?></li>
<?php } ?>
</ul>
</div>