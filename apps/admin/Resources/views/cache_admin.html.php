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

<?php $admin->title(_zm('Cache Admin')) ?>

<form action="<?php echo $net->url() ?>" method="POST" onsubmit="return ZenMagick.confirm('<?php _vzm('Clear selected caches?') ?>', this);">
  <fieldset>
    <legend><?php _vzm("Existing Caches") ?></legend>
      <table class="grid">
        <thead>
          <tr>
            <th><?php _vzm('Id') ?></th>
            <th><?php _vzm('Stats') ?></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($caches as $id => $cache) { $stats = $cache->getStats(); ?>
            <tr>
              <td>
                <input type="checkbox" id="cache_<?php echo $id ?>" name="cache_<?php echo $id ?>" value="x">
                <label for="cache_<?php echo $id ?>"><?php echo $id ?></label>
              </td>
              <td><?php echo var_dump($stats) ?></td>
            </tr>
          <?php } ?>
        </tbody>
      </table>
      <br>
      <div class="submit">
        <input class="<?php echo $buttonClasses ?>" type="submit" value="<?php _vzm('Clear selected caches') ?>">
      </div>
  </fieldset>
</form>
