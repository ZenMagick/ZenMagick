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
<?php $admin->title(_zm('Admin Users')) ?>

<table class="grid">
  <tr>
    <th><?php _vzm('ID') ?></th>
    <th><?php _vzm('Name') ?></th>
    <th><?php _vzm('Email') ?></th>
    <th><?php _vzm('Options') ?></th>
  </tr>
  <?php foreach ($resultList->getResults() as $adminUser) { ?>
    <tr>
      <td><?php echo $adminUser->getId() ?></td>
      <td><a href="<?php echo $view['router']->generate('edit_admin_user', array('adminUserId' => $adminUser->getId())) ?>"><?php echo $adminUser->getUsername() ?></a></td>
      <td><?php echo $adminUser->getEmail() ?></td>
      <td>
        <form action="<?php echo $view['router']->generate('edit_admin_user') ?>" method="post">
          <input type="hidden" name="deleteUserId" value="<?php echo $adminUser->getId() ?>">
          <input class="<?php echo $buttonClasses ?>" type="submit" value="<?php _vzm("Delete") ?>">
        </form>
      </td>
    </tr>
  <?php } ?>
</table>
<p><a class="<?php echo $buttonClasses ?>" href="<?php echo $view['router']->generate('edit_admin_user') ?>"><?php _vzm('Add User') ?></a></p>
<?php echo $view->render('AdminBundle::pagination.html.twig', array('resultList' => $resultList)); ?>
