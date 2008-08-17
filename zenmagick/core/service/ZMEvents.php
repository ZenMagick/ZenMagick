<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2008 ZenMagick
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
    private $subscriber_;
    private $eventLog;

    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
        $this->subscriber_ = array();
        $this->eventLog = array();
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
     * Attach an observer to this event source.
     *
     * @param mixed observer Reference to the observer class or method.
     */
    public function attach($observer) {
        $eventId = 'all';
        $nameHash = md5(get_class($observer).$eventId);
        $this->subscriber_[$nameHash] = array('obs'=> $observer, 'eventID'=>$eventId);
    }

    /**
     * Detach an observer from the notifier object
     *
     * @param mixed observer Reference to the observer class or method.
     */
    public function detach($observer) {
        $eventId = 'all';
        $nameHash = md5(get_class($observer).$eventId);
        unset($this->subscriber_[$nameHash]);
    }

    /**
     * Get the event log.
     *
     * @return array Log of all events and timings.
     */
    public function getEventLog() {
        return $this->eventLog;
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
     * Generic observer callback that delegates to internal methods...
     *
     * <p>The actual method called is generated based on the event id.</p>
     *
     * @param mixed notifier The event source.
     * @param string eventId The event id.
     * @param array args Optional parameter; default is <code>null</code>.
     */
    public function update($notifier, $eventId, $args=array()) {
        $method = $this->event2method($eventId, 'on');
        $this->eventLog[] = array('id' => $eventId, 'method' => $method, 'time' => ZMRuntime::getExecutionTime(), 'args' => $args);
        $this->log('fire zen-cart event: ' . $eventId . '/'.$method, ZM_LOG_DEBUG);
        foreach($this->subscriber_ as $obs) {
            if (method_exists($obs['obs'], $method)) {
                call_user_func(array($obs['obs'], $method), $args);
            }
        }
    }

    /**
     * Fire ZenMagick event.
     *
     * <p>ZenMagick event methods start with <em>onZM</em>.</p>
     *
     * <p>A reference of the event source is added to the optional arguments map with the key
     * <em>source</em>.</p>
     *
     * @param mixed source The event source.
     * @param string eventId The event id.
     * @param array args Optional parameter; default is <code>array()</code>.
     */
    public function fireEvent($source, $eventId, $args=array()) {
        $method = $this->event2method($eventId);
        $this->eventLog[] = array('id' => $eventId, 'method' => $method, 'time' => ZMRuntime::getExecutionTime(), 'args' => $args);
        $args['source'] = $source;
        $this->log('fire ZenMagick event: ' . $eventId . '/'.$method, ZM_LOG_DEBUG);
        foreach($this->subscriber_ as $obs) {
            if (method_exists($obs['obs'], $method)) {
                call_user_func(array($obs['obs'], $method), $args);
            }
        }
    }

}

?>
