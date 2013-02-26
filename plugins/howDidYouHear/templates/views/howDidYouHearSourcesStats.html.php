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
    <th><?php _vzm('Count') ?></th>
  </tr>
  <?php foreach ($resultList->getResults() as $line) { ?>
  <tr>
    <?php if (ID_SOURCE_OTHER == $line->getSourceId()) { ?>
      <td><a href="<?php echo $net->url('howDidYouHearSourcesStats', array('other' => 'true')) ?>"><?php echo $html->encode($line->getName()) ?></a></td>
    <?php } else { ?>
      <td><?php echo $html->encode($line->getName()) ?></td>
    <?php } ?>
    <td><?php echo $line->getCount() ?></td>
  </tr>
  <?php } ?>
</table>
<?php if ($isOther) { ?>
  <p><a href="<?php echo $net->url('howDidYouHearSourcesStats') ?>" class="<?php echo $buttonClasses ?>"><?php _vzm('Back to overview') ?></a></p>
<?php } ?>
<?php echo $this->fetch('pagination.html.php') ?>
