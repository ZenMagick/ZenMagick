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

<table class="grid">
  <tr>
    <th><?php _vzm('Id') ?></th>
    <th><?php _vzm('Name') ?></th>
  </tr>
  <?php foreach ($themes as $theme) { ?>
    <tr>
      <td><?php echo $theme->getThemeId() ?></td>
      <td><?php echo $theme->getName() ?></td>
    </tr>
  <?php } ?>
</table>
