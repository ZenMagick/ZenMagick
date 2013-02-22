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
<p>Groups: <?php echo implode(', ', $app->getUser()->getRoles()) ?></p>
<form action="<?php echo $net->generate('update_user') ?>" method="POST" id="updateUser">
  <fieldset>
  <legend><?php _vzm('Account') ?></legend>
    <p><label for="username"><?php _vzm('Name') ?></label> <input type="text" id="username" name="username" value="<?php echo $html->encode($updateUser->getUsername()) ?>"></p>
    <p><label for="email"><?php _vzm('Email') ?></label> <input type="text" id="email" name="email" value="<?php echo $html->encode($updateUser->getEmail()) ?>"></p>
    <p><label for="currentPassword"><?php _vzm('Current password') ?><span>*</span></label> <input type="password" id="currentPassword" name="currentPassword"></p>
    <p><label for="newPassword"><?php _vzm('New password') ?></label> <input type="password" id="newPassword" name="newPassword"></p>
    <p><label for="confirmPassword"><?php _vzm('Confirm password') ?></label> <input type="password" id="confirmPassword" name="confirmPassword"></p>
  </fieldset>
  <fieldset>
    <legend><?php _vzm('Preferences') ?></legend>
    <?php foreach ($widgets as $widget) { ?>
      <p><label for="<?php echo $widget->getId() ?>"><?php echo $widget->getTitle() ?></label> <?php echo $widget->render($request, $templateView) ?></p>
    <?php } ?>
  </fieldset>
  <p><input class="<?php echo $buttonClasses ?>" type="submit" value="<?php _vzm('Update') ?>") ?>
</form>
