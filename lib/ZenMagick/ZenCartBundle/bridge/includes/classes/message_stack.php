<?php
/*
 * ZenMagick - Another PHP framework.
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

use ZenMagick\Base\Runtime;

/**
 * ZenCart wrapper for ZenMagick's messsageService
 *
 * Currently requires 2 globals from ZenCart code:
 * $current_page_base and $template.
 *
 * @author Johnny Robeson <johnny@localmomentum.net>
 */
class messageStack {
    /**
     * Get ZenMagick message service.
     *
     * @return object
     */
    private function getService() {
        return Runtime::getContainer()->get('session')->getFlashBag();
    }

    /**
     * Clear messages.
     */
    public function reset() {
        $this->getService()->clear();
    }


    /**
     * Add a message.
     *
     * Adds a message to ZenMagick messagesService.
     *
     * It converts caution and warning to warn.
     *
     * @param string class named stack to store messages
     * @param string message message text
     * @param string type message type
     */
    public function add($class, $message, $type = 'error') {
        $type = in_array($type, array('caution', 'warning')) ? 'warn' : $type;
        $this->getService()->addMessage($message, $type, $class);
    }

    /**
     * Alias for add().
     *
     * @param string class named stack to store messages
     * @param string message message text
     * @param string type message type
     */
    public function add_session($class, $message, $type = 'error') {
        $this->add($class, $message, $type);
    }

    /**
     * Get message type icon.
     *
     * Only supports error, warning and success
     *
     * caution is equivalent to warning
     * as per the original implementation.
     *
     * @param string type message type
     * @return string html img tag
     */
    private function getIcon($type) {
        $type = $type == 'warn' ? 'warning' : $type;
        $type = strtoupper($type);

        if (defined('ICON_IMAGE_'.$type) && isset($GLOBALS['template'])) {
            $file = constant('ICON_IMAGE_'.$type);
            $alt = constant('ICON_'.$type.'_ALT');
            $path = $GLOBALS['template']->get_template_dir($file, DIR_WS_TEMPLATE, $GLOBALS['current_page_base'], 'images/icons');
            return zen_image($path.'/'.$file, $alt) . '  ';
        }
        return '';
    }

    /**
     * Display the messages directly.
     *
     * If the class is set to header then also include global
     * messages set in messageService.
     *
     * @param string class named stack to get messages
     */
    public function output($class) {
        $messages = $this->getService()->getMessages($class);
        if ('header' == $class) {
            if (null != ($global = $this->getService()->getMessages('global'))) {
                $messages = array_merge($messages, $global);
            }
        }
        $output = array();
        foreach ($messages as $message) {
            $type = $message->getType();
            if ('warn' == $type) $type = 'warning';
            $output[] = array(
                'params' => 'class="messageStack'.ucwords($type).' larger"',
                'class' => $message->getRef(),
                'text' => $this->getIcon($message->getType()).$message->getText()
            );

        }
        if (isset($GLOBALS['template'])) {
            $path = $GLOBALS['template']->get_template_dir('tpl_message_stack_default.php',DIR_WS_TEMPLATE, $GLOBALS['current_page_base'],'templates');
            require($path.'/tpl_message_stack_default.php');
        }
    }

    /**
     * Check whether messages exist before showing them.
     *
     * All of the current users only check whether the
     * there are more than 0 messages.
     *
     * If the class is set to header then also check for
     * any global messages in the messageService.
     *
     * @param string class named stack to count
     * @return int (0 or 1 only)
     */
    public function size($class) {
        $hasMessages = $this->getService()->hasMessages($class);
        if ('header' == $class) {
           $hasMessages = $hasMessages || $this->getService()->hasMessages('global');
        }
        return (int)$hasMessages;
    }
}
