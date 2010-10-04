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

<?php //var_dump($languages) ?>

<h2><?php _vzm('Configured Themes') ?></h2>
<table class="grid">
  <tr>
    <th><?php _vzm('Theme') ?></th>
    <th><?php _vzm('Variation') ?></th>
    <th><?php _vzm('Language') ?></th>
  </tr>
  <?php foreach ($themeConfig as $config) { $theme = $config->getTheme(); $variation = $config->getVariation(); ?>
    <tr>
      <td><?php echo $theme->getName() ?></td>
      <?php if (null != $variation) { ?>
          <td><?php echo $variation->getName() ?></td>
      <?php } else { ?>
          <td> - </td>
      <?php } ?>
      <td><?php echo $config->getLanguageId() ?></td>
    </tr>
  <?php } ?>
</table>

<h2><?php _vzm('Configure') ?></h2>
<form action="<?php echo $admin2->url() ?>" method="POST">
  <label for="themeId"><?php _vzm('Theme') ?></label>
  <select id="themeId" name="themeId">
    <?php foreach ($themes as $theme) { ?>
      <option value="<?php echo $theme->getThemeId() ?>"><?php echo $theme->getName() ?></option>
    <?php } ?>
  </select>
  <label for="variationId"><?php _vzm('Variation') ?></label>
  <select id="variationId" name="variationId">
    <?php foreach ($themes as $theme) { ?>
      <option value="<?php echo $theme->getThemeId() ?>"><?php echo $theme->getName() ?></option>
    <?php } ?>
  </select>
  <?php if (1 < count($languages)) { ?>
      <?php //TODO ?>
  <?php } ?>
  <input type="submit" value="<?php _vzm('Create') ?>">
</form>
