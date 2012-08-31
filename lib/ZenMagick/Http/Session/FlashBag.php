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
namespace ZenMagick\Http\Session;

use ZenMagick\Http\Messages\Message;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBag as BaseFlashBag;

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
class FlashBag extends BaseFlashBag {
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

    private $uniqueMsgRef_ = array();

    /**
     * Generic add a message.
     *
     * @param string text The message text.
     * @param string type The message type; default is <code>Messages::T_MESSAGE</code>.
     * @param string ref The referencing resource; default is <code>Messages::REF_GLOBAL</code>.
     */
    public function addMessage($text, $type=self::T_MESSAGE, $ref=self::REF_GLOBAL) {
        $key = trim($text);
        if (array_key_exists($key, $this->uniqueMsgRef_)) {
            return;
        }

        $this->uniqueMsgRef_[$key] = $text;
        $message = array('text' => $text, 'type' => $type, 'ref' => $ref);
        $this->add('zenmagick', $message);
    }

    /**
     * Add an error message.
     *
     * @param string text The message text.
     * @param string ref The referencing resource; default is <code>Messages::REF_GLOBAL</code>.
     */
    public function error($text, $ref=self::REF_GLOBAL) {
        $this->addMessage($text, self::T_ERROR, $ref);
    }

    /**
     * Add a warning message.
     *
     * @param string text The message text.
     * @param string ref The referencing resource; default is <code>Messages::REF_GLOBAL</code>.
     */
    public function warn($text, $ref=self::REF_GLOBAL) {
        $this->addMessage($text, self::T_WARN, $ref);
    }

    /**
     * Add a default message.
     *
     * @param string text The message text.
     * @param string ref The referencing resource; default is <code>Messages::REF_GLOBAL</code>.
     */
    public function msg($text, $ref=self::REF_GLOBAL) {
        $this->addMessage($text, self::T_MESSAGE, $ref);
    }

    /**
     * Add a success message.
     *
     * @param string text The message text.
     * @param string ref The referencing resource; default is <code>Messages::REF_GLOBAL</code>.
     */
    public function success($text, $ref=self::REF_GLOBAL) {
        $this->addMessage($text, self::T_SUCCESS, $ref);
    }

    /**
     * Add a group of messages.
     *
     * @param array messages List of <code>Message</code> instances.
     */
    public function addAll($messages) {
        foreach ($messages as $msg) {
            $this->addMessage($msg->getText(), $msg->getType(), $msg->getRef());
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
            return $this->has('zenmagick');
        }

        foreach ($this->peek('zenmagick') as $message) {
            if ($ref == $message['ref']) {
                return true;
              }
        }

        return false;
    }

    /**
     * Get all messages.
     *
     * @param string ref The referring resource; default is <code>null</code> for all.
     * @Param boolean clear Optional flag to clear the internal buffer; default is <code>false</code>.
     * @return array List of <code>Message</code> instances.
     */
    public function getMessages($ref=null) {
        $messages = array();
        foreach ($this->get('zenmagick') as $ii => $msg) {
            if (null == $ref || $ref == $msg['ref']) {
                $message = new Message();
                $message->setText($msg['text']);
                $message->setType($msg['type']);
                $message->setRef($msg['ref']);
                $messages[] = $message;
            }
        }

        return $messages;
    }
}
