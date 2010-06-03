<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2010 zenmagick.org
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
 * $Id$
 */
?>
<h1><?php zm_l10n("Admin Users") ?></h1>

<table>
  <tr>
    <th><?php zm_l10n('ID') ?></th>
    <th><?php zm_l10n('Name') ?></th>
    <th><?php zm_l10n('Email') ?></th>
    <th><?php zm_l10n('Demo') ?></th>
    <th><?php zm_l10n('Options') ?></th>
  </tr>
  <?php foreach ($resultList->getResults() as $adminUser) { ?>
    <tr>
      <td><?php echo $adminUser->getId() ?></td>
      <td><a href="<?php echo $admin2->url('edit_admin_user', 'adminUserId='.$adminUser->getId()) ?>"><?php echo $adminUser->getName() ?></a></td>
      <td><?php echo $adminUser->getEmail() ?></td>
      <td><?php echo ($adminUser->isDemo() ? zm_l10n_get('Demo') : zm_l10n_get('Live')) ?></td>
      <td>
        <form action="<?php echo $admin2->url('edit_admin_user') ?>" method="post">
          <input type="hidden" name="deleteUserId" value="<?php echo $adminUser->getId() ?>">
          <input type="submit" value="<?php zm_l10n("Delete") ?>">
        </form>
      </td>
    </tr>
  <?php } ?>
</table>
<p><a href="<?php echo $admin2->url('edit_admin_user') ?>">Add User</a></p>
<?php echo $this->fetch('pagination.php'); ?>
