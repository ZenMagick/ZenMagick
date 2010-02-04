<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2009 zenmagick.org
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

<script type="text/javascript">
    var statusImgOn = 'images/icon_green_on.gif';
    var statusImgOff = 'images/icon_red_on.gif';

    function toggle_status(link) {
        var currentStatus = link.className.split('-')[2];
        var pageId = link.id.split('-')[0];
        var property = link.id.split('-')[1];
        var languageId = 1;
        $.ajax({
            type: "POST",
            url: "<?php echo $net->ajax('EZPages_admin', 'setEZPageProperty') ?>",
            data: 'pageId='+pageId+'&languageId='+languageId+'&property='+property+'&value='+('on' == currentStatus ? 'false' : 'true'),
            success: function(msg) { 
                var selector = '#'+link.id+' img';
                $('#'+link.id+' img').attr('src', 'on' == currentStatus ? statusImgOff : statusImgOn);
                link.className = 'ezpage-status-'+('on' == currentStatus ? 'off' : 'on');
            },
            error: function(msg) { 
                alert(msg);
            }
        });
    }

</script>

<table cellpadding="5" cellspacing="0"> 
  <thead>
    <tr>
      <th><?php zm_l10n("Id") ?></th>
      <th><?php zm_l10n("Title") ?></th>
      <th><?php zm_l10n("New Window") ?></th>
      <th><?php zm_l10n("Secure") ?></th>
      <th><?php zm_l10n("Header") ?></th>
      <th><?php zm_l10n("Sidebar") ?></th>
      <th><?php zm_l10n("Footer") ?></th>
      <th><?php zm_l10n("Chapter") ?></th>
      <th><?php zm_l10n("TOC") ?></th>
    </tr>
  </thead>
  <tbody>
    <?php $odd = true; foreach (ZMEZPages::instance()->getAllPages() as $ezpage) { $odd = !$odd; ?>
      <tr>
        <td><?php echo $ezpage->getId() ?></td>
        <td><?php echo $html->encode($ezpage->getTitle()) ?></td>
        <td>
            <a href="#<?php echo $ezpage->getId().'-NewWin' ?>" onclick="toggle_status(this); return false;" id="<?php echo $ezpage->getId() ?>-NewWin" class="ezpage-status-<?php echo ($ezpage->isNewWin() ? 'on' : 'off') ?>"><img border="0" src="images/<?php echo ($ezpage->isNewWin() ? 'icon_green_on.gif' : 'icon_red_on.gif') ?>"></a>
        </td>
        <td>
            <a href="#<?php echo $ezpage->getId().'-SSL' ?>" onclick="toggle_status(this); return false;" id="<?php echo $ezpage->getId() ?>-SSL" class="ezpage-status-<?php echo ($ezpage->isSSL() ? 'on' : 'off') ?>"><img border="0" src="images/<?php echo ($ezpage->isSSL() ? 'icon_green_on.gif' : 'icon_red_on.gif') ?>"></a>
        </td>
        <td>
            <a href="#<?php echo $ezpage->getId().'-header' ?>" onclick="toggle_status(this); return false;" id="<?php echo $ezpage->getId() ?>-header" class="ezpage-status-<?php echo ($ezpage->isHeader() ? 'on' : 'off') ?>"><img border="0" src="images/<?php echo ($ezpage->isHeader() ? 'icon_green_on.gif' : 'icon_red_on.gif') ?>"></a>

            <?php echo $ezpage->getHeaderSort() ?>
        </td>
        <td>
            <a href="#<?php echo $ezpage->getId().'-sidebox' ?>" onclick="toggle_status(this); return false;" id="<?php echo $ezpage->getId() ?>-sidebox" class="ezpage-status-<?php echo ($ezpage->isSidebox() ? 'on' : 'off') ?>"><img border="0" src="images/<?php echo ($ezpage->isSidebox() ? 'icon_green_on.gif' : 'icon_red_on.gif') ?>"></a>
            <?php echo $ezpage->getSideboxSort() ?>
        </td>
        <td>
            <a href="#<?php echo $ezpage->getId().'-footer' ?>" onclick="toggle_status(this); return false;" id="<?php echo $ezpage->getId() ?>-footer" class="ezpage-status-<?php echo ($ezpage->isFooter() ? 'on' : 'off') ?>"><img border="0" src="images/<?php echo ($ezpage->isFooter() ? 'icon_green_on.gif' : 'icon_red_on.gif') ?>"></a>
            <?php echo $ezpage->getFooterSort() ?>
        </td>
        <td><?php echo $ezpage->getTocChapter() ?></td>
        <td>
            <a href="#<?php echo $ezpage->getId().'-toc' ?>" onclick="toggle_status(this); return false;" id="<?php echo $ezpage->getId() ?>-toc" class="ezpage-status-<?php echo ($ezpage->isToc() ? 'on' : 'off') ?>"><img border="0" src="images/<?php echo ($ezpage->isToc() ? 'icon_green_on.gif' : 'icon_red_on.gif') ?>"></a>
            <?php echo $ezpage->getTocSort() ?>
        </td>
      </tr>
    <?php } ?>
  </tbody>
</table>
