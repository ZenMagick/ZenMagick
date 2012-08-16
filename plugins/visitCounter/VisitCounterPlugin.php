<?php
/*
 * ZenMagick - Smart e-commerce
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
namespace zenmagick\plugins\visitCounter;

use zenmagick\apps\store\plugins\Plugin;
use zenmagick\base\Runtime;

/**
 * Visit Counter plugin.
 *
 * This reimplements the ZenCart site visit counter.
 */
class VisitCounterPlugin extends Plugin {

    /**
     * {@inheritDoc}
     *
     * @todo create the tables ourselves or completely drop this support.
     * @todo add index on startdate field in counter table
     * @todo convert startdate to an actual date field instead of char for both tables
     */
    public function init() {
        parent::init();
        \ZMRuntime::getDatabase()->getMapper()->setMappingForTable('counter', array(
            'startdate' => array('column' => 'startdate', 'type' => 'string'),
            'counter' => array('column' => 'counter', 'type' => 'integer'),
        ));
        \ZMRuntime::getDatabase()->getMapper()->setMappingForTable('counter_history', array(
            'id' => array('column' => 'id', 'type' => 'bigint', 'key' => true, 'auto' => true),
            'startdate' => array('column' => 'startdate', 'type' => 'string'),
            'counter' => array('column' => 'counter', 'type' => 'integer'),
            'session_counter' => array('column' => 'session_counter', 'type' => 'integer')
        ));
    }

    /**
     * Handle ZenCart page and session counting
     *
     */
    public function onControllerProcessEnd($event) {
        if (!Runtime::isContextMatch('storefront')) return;
        $conn = \ZMRuntime::getDatabase();
        $session = $event->get('request')->getSession();

        $newSession = false;
        if ($session->isStarted()) {
            $newSession = !$session->getValue('session_counter');
            if ($newSession) $session->setValue('session_counter', true);
        }
        $today  = date('Ymd');
        $query = "INSERT INTO %table.counter_history% (startdate, counter, session_counter) values (:today, 1, 1)
                 ON DUPLICATE KEY UPDATE counter = counter + 1, session_counter = session_counter + :session_counter";
        $conn->executeUpdate($query, array('today' => $today, 'session_counter' => (int)$newSession));

        // @todo add a unique index on counter table
        $query = "SELECT startdate, counter FROM %table.counter% WHERE startdate = :startdate";
        $result = $conn->querySingle($query, array('startdate' => $today), 'counter');
        if (empty($result)) {
            $conn->insert('counter', array('startdate' => $today, 'counter' => 1));
        } else {
            $query = "UPDATE %table.counter% SET counter = counter + 1";
            $conn->updateObj($query, array(), 'counter');
        }
    }
}
