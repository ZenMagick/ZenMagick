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
<div id="menu">
  <ul>
    <li class="first"><a href="<?php echo $view['router']->generate('index'); ?>"><?php _vzm("HOME") ?></a></li>
    <?php if ($session->isAnonymous()) { ?>
        <li><a href="<?php echo $view['router']->generate('login'); ?>"><?php _vzm("LOGIN") ?></a></li>
    <?php } ?>
    <?php if ($session->isRegistered()) { ?>
        <li><a href="<?php echo $view['router']->generate('account'); ?>"><?php _vzm("ACCOUNT") ?></a></li>
    <?php } ?>
    <?php if (!$session->isAnonymous()) { ?>
        <li><a href="<?php echo $view['router']->generate('logoff'); ?>"><?php _vzm("LOGOFF") ?></a></li>
    <?php } ?>
    <?php if (!$container->get('shoppingCart')->isEmpty() && !$isCheckout) { ?>
        <li><a href="<?php echo $view['router']->generate('shopping_cart'); ?>"><?php _vzm("SHOPPING CART") ?></a></li>
        <li><a href="<?php echo $view['router']->generate('checkout_shipping'); ?>"><?php _vzm("CHECKOUT") ?></a></li>
    <?php } ?>
    <?php foreach ($container->get('ezPageService')->getPagesForHeader($session->getLanguageId()) as $page) { ?>
        <li><?php echo $html->ezpageLink($page->getId()) ?></li>
    <?php } ?>
  </ul>
</div>
