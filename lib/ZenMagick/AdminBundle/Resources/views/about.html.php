<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2012 zenmagick.org
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

<div id="b_about">
  <div class="about" id="about">
  <h2><?php _vzm("About ZenMagick") ?></h2>
      <p><span class="label"><?php _vzm('Version:') ?></span> <?php echo AppKernel::APP_VERSION; ?></p>
      <p><span class="label"><?php _vzm('Homepage:') ?></span> <a href="http://www.zenmagick.org">www.zenmagick.org</a></p>
      <p><span class="label"><?php _vzm('Author:') ?></span> Martin Rademacher</p>
  </div>
  <div class="about">
  <?php
        ob_start();
        phpinfo(1);
        $info = ob_get_clean();
        $info = preg_replace('%^.*<body>(.*)</body>.*$%ms', '$1', $info);
        $info = str_replace('width="600"', '', $info);
   ?>
    <?php echo $info; ?>
    <p><?php _vzm('For the full PHP info see zen-cart\'s <a href="server_info.php">server info</a>.') ?></p>
  </div>
</div>
