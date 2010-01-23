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
        <td><?php echo ($ezpage->isNewWin() ? 'Y' : 'N') ?></td>
        <td><?php echo ($ezpage->isSSL() ? 'Y' : 'N') ?></td>
        <td>
            <?php echo ($ezpage->isHeader() ? 'Y' : 'N') ?>
            <?php echo $ezpage->getHeaderSort() ?>
        </td>
        <td>
            <?php echo ($ezpage->isSidebox() ? 'Y' : 'N') ?>
            <?php echo $ezpage->getSideboxSort() ?>
        </td>
        <td>
            <?php echo ($ezpage->isFooter() ? 'Y' : 'N') ?>
            <?php echo $ezpage->getFooterSort() ?>
        </td>
        <td><?php echo $ezpage->getTocChapter() ?></td>
        <td>
            <?php echo ($ezpage->isToc() ? 'Y' : 'N') ?>
            <?php echo $ezpage->getTocSort() ?>
        </td>
      </tr>
    <?php } ?>
  </tbody>
</table>
