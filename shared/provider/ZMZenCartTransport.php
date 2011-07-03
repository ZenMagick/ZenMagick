<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2009 Fabien Potencier <fabien.potencier@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Swift mailer transport using the legacy zencart code.
 *
 * @package zenmagick.store.shared.provider
 * @author DerManoMann
 */
class ZMZenCartTransport extends Swift_Transport_NullTransport {
    private $_eventDispatcher;

    /**
     * Constructor.
     */
    public function __construct(Swift_Events_EventDispatcher $eventDispatcher) {
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

        // TODO: call legacy code

        if ($evt) {
            $evt->setResult(Swift_Events_SendEvent::RESULT_SUCCESS);
            $this->_eventDispatcher->dispatchEvent($evt, 'sendPerformed');
        }

        return 0;
    }

}
