<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2010 zenmagick.org
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
use zenmagick\base\events\Event;

/**
 * Event service.
 *
 * <p>This service will relay *all* zen-cart events to registered listeners. Subscriber
 * have to implement the same <code>update(..)</code> as for registering with zen-cart
 * directly.</p>
 *
 * @author DerManoMann
 * @package zenmagick.store.shared.services
 */
class Events extends ZMEvents {
    const DISPATCH_START = 'dispatch_start';
    const DISPATCH_DONE = 'dispatch_done';
    const VIEW_START = 'view_start';
    const VIEW_DONE = 'view_done';
    const CONTROLLER_PROCESS_START = 'controller_process_start';
    const CONTROLLER_PROCESS_END = 'controller_process_end';
    const ALL_DONE = 'all_done';
    const FINALISE_CONTENTS = 'finalise_contents';

    const THEME_RESOLVED = 'theme_resolved';
    const CREATE_ACCOUNT = 'create_account';
    const LOGIN_SUCCESS = 'login_success';
    const LOGOFF_SUCCESS = 'logoff_success';
    const GENERATE_EMAIL = 'generate_email';
    const CREATE_ORDER = 'create_order';
    const PASSWORD_CHANGED = 'password_changed';
    const SEARCH = 'search';


    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
        $this->subscribers_ = array();
        $this->eventLog_ = array();
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Generic zen-cart observer callback that delegates to internal methods...
     *
     * <p>The actual method called is generated based on the event id.</p>
     *
     * @param mixed notifier The event source.
     * @param string eventId The event id.
     * @param array args Optional parameter; default is <code>null</code>.
     */
    public function update($notifier, $eventId, $args=array()) {
        // forward to new event dispatcher
        Runtime::getEventDispatcher()->notify(new Event($notifier, $eventId, $args));
        $method = $this->event2method($eventId, 'on');
        $args = is_array($args) ? $args : array();
        $this->eventLog_[] = array('id' => $eventId, 'method' => $method, 'time' => ZMRuntime::getExecutionTime(), 'memory' => memory_get_usage(true), 'args' => array_keys($args));
        ZMLogging::instance()->log('fire zen-cart event: ' . $eventId . '/'.$method, ZMLogging::DEBUG);
        foreach($this->subscribers_ as $subscriber) {
            if (null === $subscriber['methods']) {
                $subscriber['methods'] = get_class_methods($subscriber['obj']);
            }
            if (is_array($subscriber['methods'])) {
                if (in_array($method, $subscriber['methods'])) {
                    call_user_func(array($subscriber['obj'], $method), $args);
                }
                if (in_array('update', $subscriber['methods'])) {
                    call_user_func(array($subscriber['obj'], 'update'), $eventId, $args);
                }
            }
        }
    }

}
