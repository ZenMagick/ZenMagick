<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006 ZenMagick
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

<?php
  //Thu, 30 Nov 2006 01:50:13 +0000
  function zm_parse_rss_date($date) {
      ereg("[a-zA-Z]+, ([1-3]?[0-9]) ([a-zA-Z]+) ([0-9]{4}) .*", $date, $regs);
      return $regs[1].'/'.$regs[2].'/'.$regs[3];
  } 
?>
<?php if (!is_object($zm_rss)) { $zm_rss = new ZMRss('http://prgch205/news/?feed=rss2'); } if ($zm_rss->hasContents()) { $channel = $zm_rss->getChannel(); ?>
    <h3><a href="<?php echo $channel->getLink() ?>"<?php zm_href_target() ?>><?php zm_l10n("[More]") ?></a><?php zm_htmlencode($channel->getTitle()) ?></h3>
    <div id="sb_rss" class="box">
        <dl>
            <?php foreach ($zm_rss->getItems() as $item) { ?>
                <dt><?php echo zm_parse_rss_date($item->getPubDate()) ?></dt>
                <dd><a href="<?php echo $item->getLink() ?>"><?php zm_htmlencode($item->getTitle()); ?></a></dd>
            <?php } ?>
        </dl>
    </div>
<?php } ?>
