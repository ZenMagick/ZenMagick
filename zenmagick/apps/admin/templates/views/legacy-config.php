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
<h1><?php echo sprintf(_zm('Config: %s'), $group->getName()) ?></h1>
<form method="POST" action="<?php echo $admin2->url() ?>">
  <fieldset>
    <legend><?php echo sprintf(_zm('Config: %s'), $group->getName()) ?></legend>
    <table>
    <?php $odd = true; foreach ($groupValues as $value) { $odd = !$odd; ?>
      <tr class="<?php echo ($odd ? 'odd' : 'even') ?>">
      <?php if ($value instanceof ZMConfigValue) { ?>
        <td><span class="tt" title="<?php _vzm('Details: %s', $value->getName()) ?>|<?php echo ZMXmlUtils::encodeAttribute(_zm($value->getDescription())) ?>"><?php _vzm($value->getName()) ?></span></td>
        <td><strong>Function not supported: <?php echo $value->getSetFunction() ?></strong></td>
      <?php } else { ?>
          <td><label for="<?php echo $value->getName() ?>"><?php _vzm($value->getTitle()) ?></label></td>
          <td>
            <?php /* tooltips */ ?>
            <span class="tt" title="<?php echo sprintf(_zm('Details: %s'), $value->getTitle()).'|'.ZMXmlUtils::encodeAttribute(_zm($value->getDescription())) ?>">
              <?php echo $value->render($request); ?>
            </span>
          </td>
      <?php } ?>
      </tr>
    <?php } ?>
    </table>
    <p>
      <input type="hidden" name="groupId" value="<?php echo $group->getId() ?>">
      <input type="submit" value="<?php _vzm('Update') ?>">
    </p>
 </fieldset>
</form>
