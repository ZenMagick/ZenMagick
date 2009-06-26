<?php
/*
 * ZenMagick Core - Another PHP framework.
 * Copyright (C) 2006,2009 ZenMagick
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
 * Messages to be displayed to the user.
 *
 * <p>Messages will be saved in the session if not delivered.</p>
 *
 * <p>Code supported message levels are:</p>
 * <ul>
 *  <li><code>T_ERROR</code></li>
 *  <li><code>T_WARN</code></li>
 *  <li><code>T_SUCCESS</code></li>
 *  <li><code>T_MESSAGE</code> (this is the default if no type specified)</li>
 * </ul>
 *
 * @author DerManoMann
 * @package org.zenmagick.mvc.services.misc
 * @version $Id: ZMMessages.php 2240 2009-05-27 03:53:12Z DerManoMann $
 */
class ZMMessages extends ZMObject {
    const T_GLOBAL = 'global';
    const T_SUCCESS = 'success';
    const T_MESSAGE = 'msg';
    const T_WARN = 'warn';
    const T_ERROR = 'error';
    private $messages_;
    private $uniqueMsgRef_;


    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
        $this->messages_ = array();
        $this->uniqueMsgRef_ = array();
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }

    /**
     * Get instance.
     */
    public static function instance() {
        return ZMObject::singleton('Messages');
    }


    /**
     * Generic add a message.
     *
     * @param string text The message text.
     * @param string type The message type; default is <code>ZMMessages::T_MESSAGE</code>.
     * @param string ref The referencing resource; default is <code>ZMMessages::T_GLOBAL</code>.
     */
    public function add($text, $type=ZMMessages::T_MESSAGE, $ref=ZMMessages::T_GLOBAL) {
        if (array_key_exists($text, $this->uniqueMsgRef_))
            return;

        $this->uniqueMsgRef_[$text] = $text;
        array_push($this->messages_, ZMLoader::make("Message", $text, $type, $ref));
    }

    /**
     * Add an error message.
     *
     * @param string text The message text.
     * @param string ref The referencing resource; default is <code>ZMMessages::T_GLOBAL</code>.
     */
    public function error($text, $ref=ZMMessages::T_GLOBAL) {
        $this->add($text, ZMMessages::T_ERROR, $ref);
    }

    /**
     * Add a warning message.
     *
     * @param string text The message text.
     * @param string ref The referencing resource; default is <code>ZMMessages::T_GLOBAL</code>.
     */
    public function warn($text, $ref=ZMMessages::T_GLOBAL) {
        $this->add($text, ZMMessages::T_WARN, $ref);
    }

    /**
     * Add a default message.
     *
     * @param string text The message text.
     * @param string ref The referencing resource; default is <code>ZMMessages::T_GLOBAL</code>.
     */
    public function msg($text, $ref=ZMMessages::T_GLOBAL) {
        $this->add($text, ZMMessages::T_MESSAGE, $ref);
    }

    /**
     * Add a success message.
     *
     * @param string text The message text.
     * @param string ref The referencing resource; default is <code>ZMMessages::T_GLOBAL</code>.
     */
    public function success($text, $ref=ZMMessages::T_GLOBAL) {
        $this->add($text, ZMMessages::T_SUCCESS, $ref);
    }

    /**
     * Add a group of messages.
     *
     * @param array messages List of <code>ZMMessage</code> instances.
     */
    public function addAll($messages) {
        foreach ($messages as $msg) {
            $this->add($msg->getText(), $msg->getType(), $msg->getRef());
        }
    }

    /**
     * Checks if there are any messages available.
     *
     * @param string ref The referencing resource; default is <code>null</code> for all.
     * @return boolean <code>true</code> if messages are available, <code>false</code> if not.
     */
    public function hasMessages($ref=null) {
        if (null === $ref) {
            return 0 != count($this->messages_);
        }

        foreach ($this->messages_ as $message) {
            if ($ref == $message->getRef()) {
                return true;
              }
        }

        return false;
    }

    /**
     * Get all messages.
     *
     * @param string ref The referring resource; default is <code>null</code> for all.
     * @return array List of <code>ZMMessage</code> instances.
     */
    public function getMessages($ref=null) {
        if (null === $ref) {
            return $this->messages_;
        }

        $messages = array();
        foreach ($this->messages_ as $ii => $msg) {
            if ($ref == $msg->ref_) {
                array_push($messages, $msg);
            }
        }

        return $messages;
    }

}

?>
