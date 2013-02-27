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
$admin->title(_zm('Themes')) ?>

<h2><?php _vzm('Manage Themes') ?></h2>
<form action="<?php echo $net->generate('themes') ?>" method="POST">
  <table class="grid">
    <tr>
      <th><?php _vzm('Theme') ?></th>
      <th><?php _vzm('Variation') ?></th>
      <th><?php _vzm('Options') ?></th>
    </tr>
    <?php foreach ($themeConfigList as $config) { ?>
      <tr>
        <td>
          <select name="themeId">
            <option value=""> - </option>
            <?php foreach ($themes as $theme) { ?>
              <?php $selected = ($config->getThemeId() == $theme->getId() ? ' selected' : ''); ?>
              <option value="<?php echo $theme->getId() ?>"<?php echo $selected ?>><?php echo $theme->getName() ?></option>
            <?php } ?>
          </select>
        </td>
        <td>
          <select name="variationId">
            <option value=""> - </option>
            <?php foreach ($themes as $theme) { ?>
              <?php $selected = ($config->getVariationId() == $theme->getId() ? ' selected' : ''); ?>
              <option value="<?php echo $theme->getId() ?>"<?php echo $selected ?>><?php echo $theme->getName() ?></option>
            <?php } ?>
          </select>
        </td>
        <td>
          <?php if (1 < count($themeConfigList)) { ?>
            <input type="submit" class="<?php echo $buttonClasses ?>" name="delete" value="<?php _vzm('Delete') ?>">
          <?php } ?>
          <input type="submit" class="<?php echo $buttonClasses ?>" name="update" value="<?php _vzm('Update') ?>">
        </td>
      </tr>
    <?php } ?>
  </table>
</form>
