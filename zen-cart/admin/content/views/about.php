<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2009 ZenMagick
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
 * $Id: zmAbout.php 2560 2009-11-02 20:08:36Z dermanomann $
 */
?>

<div id="b_about">
  <div class="about" id="about">
  <h2><?php zm_l10n("About ZenMagick") ?></h2>
      <p><span class="label">Version:</span> <?php echo ZMSettings::get('zenmagick.version'); ?></p>
      <p><span class="label">Homepage:</span> <a href="http://www.zenmagick.org">www.zenmagick.org</a></p>
      <p><span class="label">Author:</span> Martin Rademacher</p>
  </div>
  <div class="about">
    <?php $toolbox->macro->phpinfo(1); ?>
    <p><?php zm_l10n('For the full PHP info see zen-cart\'s <a href="server_info.php">server info</a>.') ?></p>
  </div>
</div>
