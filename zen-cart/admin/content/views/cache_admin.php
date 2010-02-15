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
 * $Id: zmCacheAdmin.php 2647 2009-11-27 00:30:20Z dermanomann $
 */
?>
<?php

  $ii = 0; foreach (ZMCaches::instance()->getCaches() as $key => $cacheInfo) {
      if ('x' == $request->getParameter('cache_'.++$ii)) {
          $ok = $cacheInfo['instance']->clear();
          ZMMessages::instance()->add(zm_l10n_get('Clear page cache \'' . $cacheInfo['group'] . '\' ' . ($ok ? 'successful' : 'failed')), $ok ? 'msg' : 'error');
      }
  }

?>

<h2><?php zm_l10n("ZenMagick Cache Admin") ?></h2>

<form action="<?php echo $toolbox->admin->url() ?>" method="POST" onsubmit="return zm_user_confirm('Clear selected?');">
  <fieldset>
    <legend><?php zm_l10n("Existing Caches") ?></legend>
      <table cellspacing="0" cellpadding="0">
        <thead>
          <tr>
            <th>Group</th>
            <th>Type</th>
            <th>Config</th>
          </tr>
        </thead>
        <tbody>
          <?php $ii = 0; foreach (ZMCaches::instance()->getCaches() as $key => $cacheInfo) { ++$ii; ?>
            <tr>
              <td>
                  <input type="checkbox" id="cache_<?php echo $ii ?>" name="cache_<?php echo $ii ?>" value="x">
                  <label for="cache_<?php echo $ii ?>"><?php echo $cacheInfo['group'] ?></label>
              </td>
              <td><?php echo $cacheInfo['type'] ?></td>
              <td><?php print_r($cacheInfo['config']) ?></td>
            </tr>
          <?php } ?>
        </tbody>
      </table>
      <div class="submit">
          <input type="submit" value="Clear selected caches">
      </div>
  </fieldset>
</form>
