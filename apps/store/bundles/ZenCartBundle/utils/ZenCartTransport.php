<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2012 zenmagick.org
 *
 * (c) 2009 Fabien Potencier <fabien.potencier@gmail.com>
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
namespace ZenMagick\apps\store\bundles\ZenCartBundle\utils;

use ZenMagick\base\Runtime;
use ZenMagick\base\events\Event;

use Swift_Transport_NullTransport;
use Swift_DependencyContainer;
use Swift_Mime_Message;
use Swift_Events_SendEvent;


/**
 * Swift mailer transport using the legacy zencart code.
 *
 * @author DerManoMann
 */
class ZenCartTransport extends Swift_Transport_NullTransport {
    private $_eventDispatcher;

    /**
     * Constructor.
     */
    public function __construct(Swift_Events_EventDispatcher $eventDispatcher=null) {
        $eventDispatcher = Swift_DependencyContainer::getInstance()->lookup('transport.eventdispatcher');
        parent::__construct($eventDispatcher);
        // private, so keep own reference
        $this->_eventDispatcher = $eventDispatcher;
    }

    /**
     * Sends the given message.
     *
     * @param Swift_Mime_Message $message
     * @param string[] &$failedRecipients to collect failures by-reference
     *
     * @return int The number of sent emails
     */
    public function send(Swift_Mime_Message $message, &$failedRecipients = null) {
        if ($evt = $this->_eventDispatcher->createSendEvent($this, $message)) {
            $this->_eventDispatcher->dispatchEvent($evt, 'beforeSendPerformed');
            if ($evt->bubbleCancelled()) {
                return 0;
            }
        }

        // crude to/from extract
        $from = $message->getFrom();
        $fromEmail = array_pop(array_keys($from));
        $fromName = $from[$fromEmail];
        $fromName = null != $fromName ? $fromName : Runtime::getSettings()->get('storeEmailFrom');
        $to = $message->getTo();
        $toEmail = array_pop(array_keys($to));
        $toName = $to[$toEmail];
        $toName = null != $toName ? $toName : $toEmail;

        // send
        $mailFunc = function_exists('zen_mail_org') ? 'zen_mail_org' : 'zen_mail';
        $mailFunc($toName, $toEmail, $message->getSubject(), $message->getBody(), $fromName, $fromEmail, $context, $template, '');

        if ($evt) {
            $evt->setResult(Swift_Events_SendEvent::RESULT_SUCCESS);
            $this->_eventDispatcher->dispatchEvent($evt, 'sendPerformed');
        }

        return 1;
    }

}
