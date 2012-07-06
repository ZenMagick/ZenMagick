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
<!--bof-navigation display -->
<div id="navSuppWrapper">
<div id="navsuppWrapperLeft" class="back">
	<div id="navsuppWrapperLeftContent"><h3>Lorem ipsum</h3><span>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Maecenas ultricies quam eget nunc mattis ultrices.</span></div>
</div>
<div id="navsuppWrapperRight" class="back">
	<div id="navSupp" class="back">
		<h3>About Us</h3>
		<div id="navSuppContent">
			<?php
				$i = 1;
				$sizeOf = count($container->get('ezPageService')->getPagesForFooter($session->getLanguageId()));
				foreach ($container->get('ezPageService')->getPagesForFooter($session->getLanguageId()) as $page) { ?>
				<?php if(1 == $i){ ?><div class="navSuppLeft back"><ul><?php } ?>
 					<li><?php echo $html->ezpageLink($page->getId()) ?></li>
				<?php if(3 == $i){ ?> </ul></div><div class="navSuppLeft forward"><ul> <?php } ?>
				<?php if($sizeOf == $i){ ?> </ul></div>
				<?php }else if($i > 5){?>
					</ul></div>
				<?php break; } $i++; ?>
       		<?php } ?>
		</div>
	</div>
	<div id="navSuppRight" class="forward">
		<div id="navSuppRightLeft" class="back">
			<h3>Lorem ipsum</h3><span>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Maecenas ultricies quam eget nunc mattis ultrices.</span>
		</div>
		<div id="navSuppRightRight" class="forward">
			<div id="navSuppRightRightContent">
				<h3>Proud to Be</h3>
			</div>
		</div>
		<br class="clearBoth" />
	</div>
	<br class="clearBoth" />
</div>
<br class="clearBoth" />
</div>
<!--eof-navigation display -->

<!--bof-ip address display -->
<?php
if (SHOW_FOOTER_IP == '1') {
?>
<div id="siteinfoIP"><?php echo TEXT_YOUR_IP_ADDRESS . '  ' . $request->getClientIp(); ?></div>
<?php
}
?>
<!--eof-ip address display -->

<!--bof-banner #5 display -->
<div id="bannerFive"><?php echo $this->fetchBlockGroup('banners.footer2') ?></div>
<!--eof-banner #5 display -->

<!--bof- site copyright display -->
<div id="siteinfoLegal" class="legalCopyright">
  <?php _vzm('Copyright &copy; %1s %2s.', date('Y'), '<a href="'.$net->url('index').'">'.$settingsService->get('storeName').'</a>') ?>
  <?php _vzm('Powered by %1s and %2s.', '<a href="http://www.zenmagick.org/" target="_blank">ZenMagick</a>', '<a href="http://www.zen-cart.com/" target="_blank">Zen Cart</a>') ?>
</div>
<!--eof- site copyright display -->
