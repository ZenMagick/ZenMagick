<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2010 zenmagick.org
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
<?php zm_title($this, _zm('Templates')) ?>

<h2><?php _vzm('Configured Themes') ?></h2>
<form action="<?php echo $admin2->url() ?>" method="POST">
  <table class="grid">
    <tr>
      <th><?php _vzm('Theme') ?></th>
      <th><?php _vzm('Variation') ?></th>
      <th><?php _vzm('Language') ?></th>
      <th><?php _vzm('Options') ?></th>
    </tr>
    <?php foreach ($themeConfig as $config) { ?>
      <tr>
        <td>
          <select name="themeId[<?php echo $config->getLanguageId() ?>]">
            <?php foreach ($themes as $theme) { ?>
              <?php $selected = ($config->getThemeId() == $theme->getThemeId() ? ' selected' : ''); ?>
              <option value="<?php echo $theme->getThemeId() ?>"<?php echo $selected ?>><?php echo $theme->getName() ?></option>
            <?php } ?>
          </select>
        </td>
        <td>
          <select name="variationId[<?php echo $config->getLanguageId() ?>]">
            <option value=""> - </option>
            <?php foreach ($themes as $theme) { ?>
              <?php $selected = ($config->getVariationId() == $theme->getThemeId() ? ' selected' : ''); ?>
              <option value="<?php echo $theme->getThemeId() ?>"<?php echo $selected ?>><?php echo $theme->getName() ?></option>
            <?php } ?>
          </select>
        </td>
        <?php if (0 != $config->getLanguageId()) { $languageName = ZMLanguages::instance()->getLanguageForId($config->getLanguageId())->getName(); } else { $languageName = _zm('Default (All)'); } ?>
        <td><?php echo  $languageName ?></td>
        <td>
          <?php if (1 < count($themeConfig)) { ?>Delete<?php } ?>
          <input type="submit" name="update[<?php echo $config->getLanguageId() ?>]" value="<?php _vzm('Update') ?>">
        </td>
      </tr>
    <?php } ?>
  </table>
</form>
