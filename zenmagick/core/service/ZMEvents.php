<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006,2007 ZenMagick
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
 * <p>Any class can subscribe. Any method of the subscriber that matches a method name derived from
 * a zen-cartr event will be called automatically.</p.
 *
 * @author mano
 * @package net.radebatz.zenmagick.service
 * @version $Id$
 */
class ZMEvents extends ZMObject {
    var $subscriber_;

    /**
     * Default c'tor.
     */
    function ZMEvents() {
        parent::__construct();

        $this->subscriber_ = array();
    }

    /**
     * Default c'tor.
     */
    function __construct() {
        $this->ZMEvents();
    }

    /**
     * Default d'tor.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Attach an observer to this event source.
     * 
     * @param object observer Reference to the observer class.
     */
    function attach(&$observer) {
        $eventID = 'all';
        $nameHash = md5(get_class($observer).$eventID);
        $this->subscriber_[$nameHash] = array('obs'=> &$observer, 'eventID'=>$eventID);
    }

    /**
     * Detach an observer from the notifier object
     *
     * @param object observer Reference to the observer class.
     */
    function detach($observer) {
        $eventID = 'all';
        $nameHash = md5(get_class($observer).$eventID);
        unset($this->subscriber_[$nameHash]);
    }

    /**
     * Convert the event id to a method name.
     *
     * <p>Callback method names must follow the following conventions:</p>
     * <ul>
     *  <li>all methods start with the prefix <em>on</em></li>
     *  <li>all prefix <em>NOTIFY</em> is ignored</li>
     *  <li>the reminder of the method name is based on capitalized words of the original event name</li>
     * </ul>
     *
     * <p>For example, to handle the event <em>NOTIFY_MODULE_END_CREATE_ACCOUNT</em>, the method name would be
     * <code>onLoginSuccessViaCreateAccount(..)</code>.</p>
     *
     * @param string eventId The event id.
     * @return string The corresponding method name.
     */
    function event2method($eventId) {
        // id is upper case
        $method = strtolower($eventId);
        // remove 
        $method = str_replace('notify', ' ', $method);
        // '_' == word boundary
        $method = str_replace('_', ' ', $method);
        // capitalise words
        $method = ucwords($method);
        // cuddle together :)
        $method = str_replace(' ', '', $method);
        // ad 'on' prefix
        $method = 'on'.$method;
        return $method;
    }


    /**
     * Generic observer callback that delegates to internal methods...
     *
     * <p>The actual method called is generated based on the event id.</p>
     *
     * @param mixed notifier.
     * @param string eventId The event id.
     * @param array args Optional parameter.
     */
    function update(&$notifier, $eventId, $args) {
        $method = $this->event2method($eventId);
        foreach($this->subscriber_ as $obs) {
            if (method_exists($obs['obs'], $method)) {
                call_user_func(array($obs['obs'], $method), $args);
            }
        }
    }

}

?>
