<?php
/*
 * ZenMagick - Another PHP framework.
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
 * <p>Generic event service that resolves events by converting the event id to
 *  a callback method name.</p>.
 *
 * @author DerManoMann
 * @package org.zenmagick.core.services.misc
 */
class ZMEvents extends ZMObject {
    protected $subscribers_;
    protected $eventLog_;


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
     * Get instance.
     */
    public static function instance() {
        return ZMRuntime::singleton('Events');
    }


    /**
     * Attach a subscriber to this event source.
     *
     * @param mixed subscriber Reference to the subscriber instance.
     */
    public function attach($subscriber) {
        $nameHash = spl_object_hash($subscriber);
        $this->subscribers_[$nameHash] = array('obj' => $subscriber, 'methods' => null);
    }

    /**
     * Detach a subscriber.
     *
     * @param mixed subscriber Reference of the subscriber instance.
     */
    public function detach($subscriber) {
        $nameHash = spl_object_hash($subscriber);
        unset($this->subscribers_[$nameHash]);
    }

    /**
     * Get the event log.
     *
     * @return array Log of all events and timings.
     * @deprecated listen yourself!
     */
    public function getEventLog() {
        return $this->eventLog_;
    }

    /**
     * Convert the event id to a method name.
     *
     * <p>Callback method names must follow the following conventions:</p>
     * <ul>
     *  <li>all methods start with the prefix <em>onZM</em></li>
     *  <li>the reminder of the method name is based on capitalized words of the original event name</li>
     * </ul>
     *
     * <p>For example, to handle the event <em>NOTIFY_MODULE_END_CREATE_ACCOUNT</em>, the method name would be
     * <code>onNotifyLoginSuccessViaCreateAccount(..)</code>.</p>
     *
     * @param string eventId The event id.
     * @param string prefix Optional prefix; default is '<code>on</code>'.
     * @return string The corresponding method name.
     */
    protected function event2method($eventId, $prefix='onZM') {
        // id is upper case
        $method = strtolower($eventId);
        // '_' == word boundary
        $method = str_replace('_', ' ', $method);
        // capitalise words
        $method = ucwords($method);
        // cuddle together :)
        $method = str_replace(' ', '', $method);
        // ad 'on' prefix
        $method = $prefix.$method;
        return $method;
    }

    /**
     * Fire event.
     *
     * <p>Subscriber may opt to return the passed in <code>$args</code> array or a modified version or <code>null</code>.</p>
     * <p>If <code>null</code> (or nothing) is returned, the orignal argument array is kept, if the return value is of
     * type array it is used as new <code>$args</code> for the next subscriber call and as final return value.</p>
     *
     * <p>ZenMagick event methods start with <em>onZM</em>.</p>
     *
     * <p>A reference of the event source is added to the optional arguments map with the key
     * <em>source</em>.</p>
     *
     * @param mixed source The event source.
     * @param string eventId The event id.
     * @param array args Optional parameter; default is <code>array()</code>.
     * @param boolean log Optional parameter to enable/disable event logging; default is <code>true</code>.
     * @return array The final <code>$args</code>.
     */
    public function fireEvent($source, $eventId, $args=array(), $log=true) {
        // forward to new event dispatcher
        Runtime::getEventDispatcher()->notify(new Event($source, $eventId, $args));
        $method = $this->event2method($eventId);
        $this->eventLog_[] = array(
            'id' => $eventId,
            'method' => $method,
            'time' => ZMRuntime::getExecutionTime(),
            'memory' => memory_get_usage(true),
            'args' => $args
        );
        $args['source'] = $source;
        if ($log) {
            ZMLogging::instance()->log('fire ZenMagick event'.(null!=$source?' ('.get_class($source).')':'').': ' . $eventId . '/'.$method, ZMLogging::DEBUG);
        }
        foreach($this->subscribers_ as $subscriber) {
            if (null === $subscriber['methods']) {
                $subscriber['methods'] = get_class_methods($subscriber['obj']);
            }
            if (null !== $subscriber['methods'] && in_array($method, $subscriber['methods'])) {
                $result = call_user_func(array($subscriber['obj'], $method), $args);
                if (null !== $result) {
                    $args = $result;
                }
            }
        }

        return $args;
    }

}
