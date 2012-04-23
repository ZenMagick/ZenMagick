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
use zenmagick\http\messages\Messages;

if ($messageService->hasMessages()) { ?>
    <ul id="messages" class="ui-widget">
    <?php
      $messageClass = array(
          Messages::T_SUCCESS => array('ui-state-default', 'ui-icon ui-icon-check'),
          Messages::T_MESSAGE => array('ui-state-default', 'ui-icon ui-icon-info'),
          Messages::T_WARN => array('ui-state-highlight', 'ui-icon ui-icon-alert'),
          Messages::T_ERROR => array('ui-state-error', 'ui-icon ui-icon-alert')
      );
    ?>
    <?php foreach ($messageService->getMessages() as $message) { ?>
        <li class="ui-corner-all <?php echo $messageClass[$message->getType()][0] ?>"><span class="<?php echo $messageClass[$message->getType()][1] ?>" style="float:left;margin-right:0.3em;"></span><?php echo $message->getText() ?></li>
    <?php } ?>
    </ul>
<?php } ?>
