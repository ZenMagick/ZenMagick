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
use ZenMagick\apps\store\Model\ConfigValue;

/**
 * Encode XML attribute characters.
 *
 * @param string s The input string.
 * @return string The encoded string.
 */
function _encodeAttribute($s) {
    $encoding = array(
        '"' => '&#34;',
        "'" => '&#39;'
    );

    foreach ($encoding as $char => $entity) {
        $s = str_replace($char, $entity, $s);
    }

    return $s;
}

$admin->title($group->getName()) ?>

<form method="POST" action="<?php echo $net->url() ?>">
  <fieldset>
    <legend><?php echo sprintf(_zm('Config: %s'), $group->getName()) ?></legend>
    <table class="grid" width="80%">
    <?php foreach ($groupValues as $value) { ?>
      <tr>
      <?php if ($value instanceof ConfigValue) { ?>
        <td><span class="tt" title="<?php _vzm('%s', $value->getName()) ?>|<?php echo _encodeAttribute(_zm($value->getDescription())) ?>"><?php _vzm($value->getName()) ?></span></td>
        <td><strong>Function not supported: <?php echo $value->getSetFunction() ?></strong></td>
      <?php } else { ?>
          <td><label for="<?php echo $value->getName() ?>"><?php _vzm($value->getTitle()) ?></label></td>
          <td>
            <?php /* tooltips */ ?>
            <span class="tt" title="<?php echo sprintf(_zm('%s'), $value->getTitle()).'|'._encodeAttribute(_zm($value->getDescription())) ?>">
              <?php echo $value->render($request, $view); ?>
            </span>
          </td>
      <?php } ?>
      </tr>
    <?php } ?>
    </table>
    <p>
      <input type="hidden" name="groupId" value="<?php echo $group->getId() ?>">
      <input type="submit" class="<?php echo $buttonClasses ?>" value="<?php _vzm('Update') ?>">
    </p>
 </fieldset>
</form>
