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
class ZMMessages extends ZMDao {
    var $messages_;
    var $uniqueMsgRef_;


    /**
     * Default c'tor.
     */
    function ZMMessages() {
    global $messageStack;

        parent::__construct();

        $this->messages_ = array();
        $this->uniqueMsgRef_ = array();
        $this->loadMessageStack();
    }

    /**
     * Default c'tor.
     */
    function __construct() {
        $this->ZMMessages();
    }

    /**
     * Default d'tor.
     */
    function __destruct() {
        parent::__destruct();
    }

    /**
     * Load messages from zen-cart message stack.
     */
    function loadMessageStack() {
    global $messageStack;

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

    /**
     * Add a message.
     *
     * @param string text The message text.
     * @param string type The message type; default is 'error'.
     */
    function add($text, $type='error') {
        if (array_key_exists($text, $this->uniqueMsgRef_))
            return;
        $this->uniqueMsgRef_[$text] = $text;
        array_push($this->messages_, $this->create("Message", $text, $type));
    }

    /**
     * Add a group of messages.
     *
     * @param array messages List of <code>ZMMessage</code> instances.
     */
    function addAll($messages) {
        foreach ($messages as $msg) {
            $this->add($msg->getText(), $msg->getType());
        }
    }

    /**
     * Checks if there are any messages available.
     *
     * @return bool <code>true</code> if messages are available, <code>false</code> if not.
     */
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

    /**
     * Get all messages.
     *
     * @return array List of <code>ZMMessage</code> instances.
     */
    function getMessages() { return $this->messages_; }

}

?>
