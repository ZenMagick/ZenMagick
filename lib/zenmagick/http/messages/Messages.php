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
namespace zenmagick\http\messages;

use zenmagick\base\Runtime;
use zenmagick\base\ZMObject;

/**
 * Application message services.
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
 */
class Messages extends ZMObject {
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
    public function __construct() {
        parent::__construct();
        $this->clear();
    }


    /**
     * Generic add a message.
     *
     * @param string type The message type; default is <code>Messages::T_MESSAGE</code>.
     * @param string text The message text.
     * @param string ref The referencing resource; default is <code>Messages::REF_GLOBAL</code>.
     */
    public function add($type=self::T_MESSAGE, $text, $ref=self::REF_GLOBAL) {
        $key = trim($text);
        if (array_key_exists($key, $this->uniqueMsgRef_)) {
            return;
        }

        $this->uniqueMsgRef_[$key] = $text;
        $message = new Message();
        $message->setText($text);
        $message->setType($type);
        $message->setRef($ref);
        $this->messages_[] = $message;
    }

    /**
     * Add an error message.
     *
     * @param string text The message text.
     * @param string ref The referencing resource; default is <code>Messages::REF_GLOBAL</code>.
     */
    public function error($text, $ref=self::REF_GLOBAL) {
        $this->add(self::T_ERROR, $text, $ref);
    }

    /**
     * Add a warning message.
     *
     * @param string text The message text.
     * @param string ref The referencing resource; default is <code>Messages::REF_GLOBAL</code>.
     */
    public function warn($text, $ref=self::REF_GLOBAL) {
        $this->add(self::T_WARN, $text, $ref);
    }

    /**
     * Add a default message.
     *
     * @param string text The message text.
     * @param string ref The referencing resource; default is <code>Messages::REF_GLOBAL</code>.
     */
    public function msg($text, $ref=self::REF_GLOBAL) {
        $this->add(self::T_MESSAGE, $text, $ref);
    }

    /**
     * Add a success message.
     *
     * @param string text The message text.
     * @param string ref The referencing resource; default is <code>Messages::REF_GLOBAL</code>.
     */
    public function success($text, $ref=self::REF_GLOBAL) {
        $this->add(self::T_SUCCESS, $text, $ref);
    }

    /**
     * Add a group of messages.
     *
     * @param array messages List of <code>Message</code> instances.
     */
    public function addAll($messages) {
        foreach ($messages as $msg) {
            $this->add($msg->getType(),$msg->getText(), $msg->getRef());
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
     * @return array List of <code>Message</code> instances.
     */
    public function getMessages($ref=null, $clear=false) {
        if (null === $ref) {
            return $this->messages_;
        }

        $messages = array();
        foreach ($this->messages_ as $ii => $msg) {
            if ($ref == $msg->getRef()) {
                $messages[] = $msg;
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
        $session->setValue('http.messages', $data);
    }

    /**
     * Load messages from session.
     *
     * @param zenmagick\http\session\Session session The current session.
     */
    public function loadMessages($session) {
        if (null !== ($data = $session->getValue('http.messages')) && is_array($data)) {
            foreach ($data as $msg) {
                $this->add($msg['type'], $msg['text'], $msg['ref']);
            }
            $session->setValue('http.messages', null);
        }
    }

}
