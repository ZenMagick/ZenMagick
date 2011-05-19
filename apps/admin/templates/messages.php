<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2011 zenmagick.org
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
<?php if (ZMMessages::instance()->hasMessages()) { ?>
    <ul id="messages" class="ui-widget">
    <?php
      $messageClass = array(
          ZMMessages::T_SUCCESS => array('ui-state-default', 'ui-icon ui-icon-check'),
          ZMMessages::T_MESSAGE => array('ui-state-default', 'ui-icon ui-icon-info'),
          ZMMessages::T_WARN => array('ui-state-highlight', 'ui-icon ui-icon-alert'),
          ZMMessages::T_ERROR => array('ui-state-error', 'ui-icon ui-icon-alert')
      );
    ?>
    <?php foreach (ZMMessages::instance()->getMessages() as $message) { ?>
        <li class="ui-corner-all <?php echo $messageClass[$message->getType()][0] ?>"><span class="<?php echo $messageClass[$message->getType()][1] ?>" style="float:left;margin-right:0.3em;"></span><?php echo $message->getText() ?></li>
    <?php } ?>
    </ul>
<?php } ?>
