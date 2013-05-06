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
<?php $view->extend('AdminBundle::default_layout.html.twig'); ?>
<?php $admin->title() ?>

<table class="grid">
  <tr>
    <th><?php _vzm('Block group id') ?></th>
    <th><?php _vzm('Options') ?></th>
  </tr>
  <?php foreach ($blockGroups as $groupName) { ?>
    <tr>
      <td><?php echo $groupName ?></td>
      <td>
      <form class="button-form" action="<?php echo $view['router']->generate('block_groups') ?>" method="POST" onsubmit="return ZenMagick.confirm('<?php _vzm("Remove group '%s'", $groupName) ?>', this);">
          <input type="hidden" name="groupName" value="<?php echo $groupName ?>">
          <input type="hidden" name="action" value="removeGroup">
          <input type="submit" class="<?php echo $buttonClasses ?>" value="Remove">
        </form>
        <a href="<?php echo $view['router']->generate('block_group_admin', array('groupName' => $groupName)) ?>" class="<?php echo $buttonClasses ?>"><?php _vzm('Configure') ?></a>
      </td>
    </tr>
  <?php } ?>
  <tr>
    <td colspan="2">
      <form action="<?php echo $view['router']->generate('block_groups') ?>" method="POST">
        <input type="hidden" name="action" value="addGroup">
        <input type="text" id="groupName" name="groupName" value="">
        <input type="submit" class="<?php echo $buttonClasses ?>" value="Add group">
      </form>
    </td>
  </tr>
</table>
