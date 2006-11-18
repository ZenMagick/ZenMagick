<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006 ZenMagick
 *
 * Portions Copyright (c) 2003 The zen-cart developers
 * Portions Copyright (c) 2003 osCommerce
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
<?php


/**
 * Messages.
 *
 * @author mano
 * @package net.radebatz.zenmagick.dao
 * @version $Id$
 */
class ZMMessages {
    var $messages_;
    var $uniqueMsgRef_;

    // create new instance
    function ZMMessages() {
    global $messageStack;
        $this->messages_ = array();
        $this->uniqueMsgRef_ = array();
        if (isset($messageStack) && isset($messageStack->messages)) {
            foreach ($messageStack->messages as $zenMessage) {
                $pos = strpos($zenMessage['text'], "/>");
                $text = substr($zenMessage['text'], $pos+2);
                $this->add($text, 
                  (false === strpos($zenMessage['params'], 'Error') 
                    ? (false === strpos($zenMessage['params'], 'Success') ? "warn" : "msg") : "error"));
            }
        }
    }

    // create new instance
    function __construct() {
        $this->ZMMessages();
    }

    function __destruct() {
    }


    // getter/setter
    function add($text, $type='error') {
        if (array_key_exists($text, $this->uniqueMsgRef_))
            return;
        $this->uniqueMsgRef_[$text] = $text;
        array_push($this->messages_, new ZMMessage($text, $type));
    }

    function hasMessages($type=null) {
        if (null == $type) {
            return 0 != count($this->messages_);
        }

        foreach ($this->messages_ as $message) {
            if ($type == $message->type_)
                return true;
        }

        return false;
    }

    function getMessages() { return $this->messages_; }

}

?>
