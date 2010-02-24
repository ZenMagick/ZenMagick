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
<?php

  $currentLanguage = Runtime::getLanguage();
  $selectedLanguageId = $request->getParameter('languageId', $currentLanguage->getId());

?>

<script type="text/javascript">
    var statusImgOn = 'images/icon_green_on.gif';
    var statusImgOff = 'images/icon_red_on.gif';

    function toggle_status(link) {
        var currentStatus = link.className.split('-')[2];
        var pageId = link.id.split('-')[0];
        var property = link.id.split('-')[1];
        var languageId = $('#languageId option:selected')[0].value;
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

<form action="<?php echo $toolbox->admin->url() ?>" method="GET">
  <input type="hidden" name="main_page" value="ezpages">
  <h2>EZPage Manager (
          <select id="languageId" name="languageId" onchange="this.form.submit();">
            <?php foreach (ZMLanguages::instance()->getLanguages() as $lang) { ?>
              <?php $selected = $selectedLanguageId == $lang->getId() ? ' selected="selected"' : ''; ?>
              <option value="<?php echo $lang->getId() ?>"<?php echo $selected ?>><?php echo $lang->getName() ?></option>
            <?php } ?>
          </select>
        )
      <a href="<?php echo $admin->url(null, 'editId=0&languageId='.$selectedLanguageId) ?>">Create new</a>
  </h2>
</form>

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
      <th></th>
    </tr>
  </thead>
  <tbody>
    <?php $odd = true; foreach (ZMEZPages::instance()->getAllPages($selectedLanguageId) as $ezPage) { $odd = !$odd; ?>
      <tr>
        <td><?php echo $ezPage->getId() ?></td>
        <td><?php echo $html->encode($ezPage->getTitle()) ?></td>
        <td>
            <a href="#<?php echo $ezPage->getId().'-NewWin' ?>" onclick="toggle_status(this); return false;" id="<?php echo $ezPage->getId() ?>-NewWin" class="ezpage-status-<?php echo ($ezPage->isNewWin() ? 'on' : 'off') ?>"><img border="0" src="images/<?php echo ($ezPage->isNewWin() ? 'icon_green_on.gif' : 'icon_red_on.gif') ?>"></a>
        </td>
        <td>
            <a href="#<?php echo $ezPage->getId().'-SSL' ?>" onclick="toggle_status(this); return false;" id="<?php echo $ezPage->getId() ?>-SSL" class="ezpage-status-<?php echo ($ezPage->isSSL() ? 'on' : 'off') ?>"><img border="0" src="images/<?php echo ($ezPage->isSSL() ? 'icon_green_on.gif' : 'icon_red_on.gif') ?>"></a>
        </td>
        <td>
            <a href="#<?php echo $ezPage->getId().'-header' ?>" onclick="toggle_status(this); return false;" id="<?php echo $ezPage->getId() ?>-header" class="ezpage-status-<?php echo ($ezPage->isHeader() ? 'on' : 'off') ?>"><img border="0" src="images/<?php echo ($ezPage->isHeader() ? 'icon_green_on.gif' : 'icon_red_on.gif') ?>"></a>

            <?php echo $ezPage->getHeaderSort() ?>
        </td>
        <td>
            <a href="#<?php echo $ezPage->getId().'-sidebox' ?>" onclick="toggle_status(this); return false;" id="<?php echo $ezPage->getId() ?>-sidebox" class="ezpage-status-<?php echo ($ezPage->isSidebox() ? 'on' : 'off') ?>"><img border="0" src="images/<?php echo ($ezPage->isSidebox() ? 'icon_green_on.gif' : 'icon_red_on.gif') ?>"></a>
            <?php echo $ezPage->getSideboxSort() ?>
        </td>
        <td>
            <a href="#<?php echo $ezPage->getId().'-footer' ?>" onclick="toggle_status(this); return false;" id="<?php echo $ezPage->getId() ?>-footer" class="ezpage-status-<?php echo ($ezPage->isFooter() ? 'on' : 'off') ?>"><img border="0" src="images/<?php echo ($ezPage->isFooter() ? 'icon_green_on.gif' : 'icon_red_on.gif') ?>"></a>
            <?php echo $ezPage->getFooterSort() ?>
        </td>
        <td><?php echo $ezPage->getTocChapter() ?></td>
        <td>
            <a href="#<?php echo $ezPage->getId().'-toc' ?>" onclick="toggle_status(this); return false;" id="<?php echo $ezPage->getId() ?>-toc" class="ezpage-status-<?php echo ($ezPage->isToc() ? 'on' : 'off') ?>"><img border="0" src="images/<?php echo ($ezPage->isToc() ? 'icon_green_on.gif' : 'icon_red_on.gif') ?>"></a>
            <?php echo $ezPage->getTocSort() ?>
        </td>
        <td>
          <a href="<?php echo $admin->url(null, 'editId='.$ezPage->getId().'&languageId='.$selectedLanguageId) ?>">Edit</a>
          <form action="<?php echo $toolbox->admin->url() ?>" method="POST">
            <input type="hidden" name="main_page" value="ezpages">
            <input type="hidden" name="languageId" value="<?php echo $selectedLanguageId ?>">
            <input type="hidden" name="deleteId" value="<?php echo $ezPage->getId() ?>">
            <input type="submit" value="Delete">
          </form>
        </td>
      </tr>
    <?php } ?>
  </tbody>
</table>

