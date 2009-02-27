<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2009 ZenMagick
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
 * Event service.
 *
 * <p>This service will relay *all* zen-cart events to registered listeners. Subscriber
 * have to implement the same <code>update(..)</code> as for registering with zen-cart
 * directly.</p>
 *
 * <p>Any class can subscribe. Any method of the subscriber class that matches a method
 * name derived from a zen-cart event will be called automatically.</p>.
 *
 * @author DerManoMann
 * @package org.zenmagick.service
 * @version $Id$
 */
class ZMEvents extends ZMObject {
    const BOOTSTRAP_DONE = 'bootstrap_done';
    const INIT_DONE = 'init_done';
    const DISPATCH_START = 'dispatch_start';
    const DISPATCH_DONE = 'dispatch_done';
    const VIEW_START = 'view_start';
    const VIEW_DONE = 'view_done';
    const CONTROLLER_PROCESS_START = 'controller_process_start';
    const CONTROLLER_PROCESS_END = 'controller_process_end';
    const THEME_RESOLVED = 'theme_resolved';
    const ALL_DONE = 'all_done';
    const CREATE_ACCOUNT = 'create_account';
    const LOGIN_SUCCESS = 'login_success';
    const LOGOFF_SUCCESS = 'logoff_success';
    const GENERATE_EMAIL = 'generate_email';
    const CREATE_ORDER = 'create_order';
    const FINALISE_CONTENTS = 'finalise_contents';
    const PASSWORD_CHANGED = 'password_changed';

    private $subscribers_;
    private $eventLog_;


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
        return ZMObject::singleton('Events');
    }


    /**
     * Attach a subscriber to this event source.
     *
     * @param mixed subscriber Reference to the subscriber instance.
     */
    public function attach($subscriber) {
        //XXX: this is not safe!
        $nameHash = md5(get_class($subscriber));
        $this->subscribers_[$nameHash] = array('obj' => $subscriber, 'methods' => null);
    }

    /**
     * Detach a subscriber.
     *
     * @param mixed subscriber Reference of the subscriber instance.
     */
    public function detach($subscriber) {
        //XXX: this is not safe!
        $nameHash = md5(get_class($subscriber));
        unset($this->subscribers_[$nameHash]);
    }

    /**
     * Get the event log.
     *
     * @return array Log of all events and timings.
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
     * Generic zen-cart observer callback that delegates to internal methods...
     *
     * <p>The actual method called is generated based on the event id.</p>
     *
     * @param mixed notifier The event source.
     * @param string eventId The event id.
     * @param array args Optional parameter; default is <code>null</code>.
     */
    public function update($notifier, $eventId, $args=array()) {
        $method = $this->event2method($eventId, 'on');
        $this->eventLog_[] = array('id' => $eventId, 'method' => $method, 'time' => ZMRuntime::getExecutionTime(), 'args' => $args);
        ZMLogging::instance()->log('fire zen-cart event: ' . $eventId . '/'.$method, ZMLogging::DEBUG);
        foreach($this->subscribers_ as $subscriber) {
            if (null === $subscriber['methods']) {
                $subscriber['methods'] = get_class_methods($subscriber['obj']);
            }
            if (in_array($method, $subscriber['methods'])) {
                call_user_func(array($subscriber['obj'], $method), $args);
            }
            if (in_array('update', $subscriber['methods'])) {
                call_user_func(array($subscriber['obj'], 'update'), $eventId, $args);
            }
        }
    }

    /**
     * Fire ZenMagick event.
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
     * @return array The final <code>$args</code>.
     */
    public function fireEvent($source, $eventId, $args=array()) {
        $method = $this->event2method($eventId);
        $this->eventLog_[] = array('id' => $eventId, 'method' => $method, 'time' => ZMRuntime::getExecutionTime(), 'args' => $args);
        $args['source'] = $source;
        ZMLogging::instance()->log('fire ZenMagick event: ' . $eventId . '/'.$method, ZMLogging::DEBUG);
        foreach($this->subscribers_ as $subscriber) {
            if (null === $subscriber['methods']) {
                $subscriber['methods'] = get_class_methods($subscriber['obj']);
            }
            if (in_array($method, $subscriber['methods'])) {
                $result = call_user_func(array($subscriber['obj'], $method), $args);
                if (null !== $result) {
                    $args = $result;
                }
            }
        }

        return $args;
    }

}

?>
