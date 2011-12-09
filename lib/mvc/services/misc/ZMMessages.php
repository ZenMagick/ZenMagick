<?php
/*
 * ZenMagick - Another PHP framework.
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
<?php

use zenmagick\base\Runtime;
use zenmagick\base\ZMObject;

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
 * @author DerManoMann <mano@zenmagick.org>
 * @package org.zenmagick.mvc.services.misc
 */
class ZMMessages extends ZMObject {
    /** Catch all (global) message reference type. */
    const REF_GLOBAL = 'global';
    /** Message type <em>success</em>. */
    const T_SUCCESS = 'success';
    /** Message type <em>message</em>. */
    const T_MESSAGE = 'msg';
    /** Message type <em>warn</em>. */
    const T_WARN = 'warn';
    /** Message type <em>error</em>. */
    const T_ERROR = 'error';

    private $messages_;
    private $uniqueMsgRef_;


    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
        $this->clear();
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
        return Runtime::getContainer()->get('messageService');
    }


    /**
     * Generic add a message.
     *
     * @param string text The message text.
     * @param string type The message type; default is <code>ZMMessages::T_MESSAGE</code>.
     * @param string ref The referencing resource; default is <code>ZMMessages::REF_GLOBAL</code>.
     */
    public function add($text, $type=self::T_MESSAGE, $ref=self::REF_GLOBAL) {
        //$key = $type.':'.trim($text);
        $key = trim($text);
        if (array_key_exists($key, $this->uniqueMsgRef_)) {
            return;
        }

        $this->uniqueMsgRef_[$key] = $text;
        $message = Runtime::getContainer()->get('ZMMessage');
        $message->setText($text);
        $message->setType($type);
        $message->setRef($ref);
        $this->messages_[] = $message;
    }

    /**
     * Add an error message.
     *
     * @param string text The message text.
     * @param string ref The referencing resource; default is <code>ZMMessages::REF_GLOBAL</code>.
     */
    public function error($text, $ref=self::REF_GLOBAL) {
        $this->add($text, self::T_ERROR, $ref);
    }

    /**
     * Add a warning message.
     *
     * @param string text The message text.
     * @param string ref The referencing resource; default is <code>ZMMessages::REF_GLOBAL</code>.
     */
    public function warn($text, $ref=self::REF_GLOBAL) {
        $this->add($text, self::T_WARN, $ref);
    }

    /**
     * Add a default message.
     *
     * @param string text The message text.
     * @param string ref The referencing resource; default is <code>ZMMessages::REF_GLOBAL</code>.
     */
    public function msg($text, $ref=self::REF_GLOBAL) {
        $this->add($text, self::T_MESSAGE, $ref);
    }

    /**
     * Add a success message.
     *
     * @param string text The message text.
     * @param string ref The referencing resource; default is <code>ZMMessages::REF_GLOBAL</code>.
     */
    public function success($text, $ref=self::REF_GLOBAL) {
        $this->add($text, self::T_SUCCESS, $ref);
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
     * Clear all messages.
     */
    public function clear() {
        $this->messages_ = array();
        $this->uniqueMsgRef_ = array();
    }

    /**
     * Get all messages.
     *
     * @param string ref The referring resource; default is <code>null</code> for all.
     * @Param boolean clear Optional flag to clear the internal buffer; default is <code>false</code>.
     * @return array List of <code>ZMMessage</code> instances.
     */
    public function getMessages($ref=null, $clear=false) {
        if (null === $ref) {
            return $this->messages_;
        }

        $messages = array();
        foreach ($this->messages_ as $ii => $msg) {
            if ($ref == $msg->ref_) {
                array_push($messages, $msg);
            }
        }

        if ($clear) {
            $this->clear();
        }

        return $messages;
    }

    /**
     * Save messages in session.
     *
     * @param zenmagick\http\session\Session session The current session.
     */
    public function saveMessages($session) {
        $data = array();
        foreach ($this->getMessages() as $msg) {
            $data[] = array('text' => $msg->getText(), 'type' => $msg->getType(), 'ref' => $msg->getRef());
        }
        $session->setValue('messages', $data, 'zenmagick.mvc');
    }

    /**
     * Load messages from session.
     *
     * @param zenmagick\http\session\Session session The current session.
     */
    public function loadMessages($session) {
        if (null !== ($data = $session->getValue('messages', 'zenmagick.mvc')) && is_array($data)) {
            foreach ($data as $msg) {
                $this->add($msg['text'], $msg['type'], $msg['ref']);
            }
            $session->setValue('messages', null, 'zenmagick.mvc');
        }
    }

}
