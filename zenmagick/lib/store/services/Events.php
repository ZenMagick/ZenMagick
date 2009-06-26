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
 * @author DerManoMann
 * @package org.zenmagick.store.services
 * @version $Id: ZMEvents.php 2332 2009-06-26 04:05:17Z dermanomann $
 */
class Events extends ZMEvents {

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
        $method = $this->event2method($eventId, 'on');
        $this->eventLog_[] = array('id' => $eventId, 'method' => $method, 'time' => Runtime::getExecutionTime(), 'args' => $args);
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

}

?>
