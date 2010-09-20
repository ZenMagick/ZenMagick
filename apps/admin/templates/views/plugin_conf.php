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

<form id="ajax-form" action="<?php echo $admin2->url() ?>" method="POST">
  <input type="hidden" name="pluginId" value="<?php echo $plugin->getId() ?>">
  <input type="hidden" name="group" value="<?php echo $plugin->getGroup() ?>">
  <input type="hidden" name="action" value="update">
  <?php foreach ($plugin->getConfigValues(false) as $value) { if ($value->isHidden() || in_array($value->getName(), array('ENABLED', 'SORT_ORDER'))) { continue; } ?>
    <fieldset style="width:94%;">
      <legend><?php echo $value->getTitle() ?></legend>
      <p><?php echo $value->getDescription() ?></p>
      <p><?php echo $value->render($request) ?></p>
    </fieldset>
  <?php } ?>
  <input class="<?php echo $buttonClasses ?>" type="submit" value="<?php _vzm("Update Settings") ?>">
</form>
