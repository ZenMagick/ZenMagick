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
<?php if (0 < count($themeList)) { ?>
  <div id="theme-switcher" style="text-align:right;padding:2px 8px;">
    <label for="selected-theme">Current Theme: </label><select id="selected-theme" name="selectedTheme" onchange="window.location = this.value;">
      <?php foreach ($themeList as $info) { ?>
      <option value="<?php echo $info['url'] ?>"<?php echo ($info['active'] ? ' selected="selected"' : '') ?>><?php echo $info['name'] ?></option>
      <?php } ?>
    </select>
  </div>
<?php } ?>
