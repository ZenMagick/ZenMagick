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
 */ $admin->title() ?>

<table class="grid" width="50%">
  <tr>
    <th><?php _vzm('Source') ?></th>
    <th><?php _vzm('Options') ?></th>
  </tr>
  <?php foreach ($resultList->getResults() as $line) { ?>
  <tr>
    <td><?php echo $html->encode($line->getName()) ?></td>
    <td>
      <a href="" class="<?php echo $buttonClasses ?>"><?php _vzm('Edit') ?></a>
      <?php if (ID_SOURCE_OTHER != $line->getSourceId()) { ?>
        <form class="button-form" action="<?php echo $net->url('howDidYouHearSourcesAdmin') ?>" method="POST" onsubmit="return ZenMagick.confirm('<?php _vzm('Are you sure?') ?>', this);">
          <input type="hidden" name="action" value="delete">
          <input type="hidden" name="sourceId" value="<?php echo $line->getSourceId() ?>">
          <input type="submit" class="<?php echo $buttonClasses ?>" value="<?php _vzm('Delete') ?>">
        </form>
      <?php } ?>
    </td>
  </tr>
  <?php } ?>
  <tr>
    <td colspan="2">
      <form action="<?php echo $net->url('howDidYouHearSourcesAdmin') ?>" method="POST">
        <input type="hidden" name="action" value="create">
        <label for="source"><?php _vzm('New Source') ?></label> <input type="text" id="source" name="source" value="">
        <input type="submit" class="<?php echo $buttonClasses ?>" value="<?php _vzm('Create') ?>">
      </form>
    </td>
  </tr>
</table>
<?php echo $this->fetch('pagination.html.php') ?>
