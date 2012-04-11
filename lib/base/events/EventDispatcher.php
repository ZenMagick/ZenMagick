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
namespace zenmagick\base\events;

use stdClass;
use \Symfony\Component\EventDispatcher\Event;
use \Symfony\Component\EventDispatcher\EventDispatcher as SymfonyEventDispatcher;

/**
 * The ZenMagick event service.
 *
 * <p>Extends the <em>Symfony event dispatcher</em>, adding:</p>
 * <ul>
 *  <li>The option to listen to <strong>all</strong> events. Reflection is used to determine if a corresponding event callback exists.</li>
 *  <li>Support for PHP callable.</li>
 * </ul>
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class EventDispatcher extends SymfonyEventDispatcher {
    const LISTEN_ALL = '*';


    /**
     * Listen to <strong>all</strong> events.
     *
     * @param mixed listener A PHP object instance or PHP callable.
     * @param integer priority The higher this value, the earlier an event listener will be triggered in the chain; default is 0.
     */
    public function listen($listener, $priority=0) {
        if (is_array($listener)) {
            $tmp = new stdClass();
            $tmp->callable = $listener;
            $listener = $tmp;
        }
        $this->addListener(EventDispatcher::LISTEN_ALL, $listener, $priority);
    }

    /**
     * {@inheritDoc}
     */
    public function dispatch($eventName, Event $event = null) {
        // use hasListeners rather than looking at the private listeners property
        if (!$this->hasListeners($eventName)) {
            return;
        }
if (false) {
    echo $eventName.": ".count($this->getListeners($eventName))."<BR>";
    foreach ($this->getListeners($eventName) as $listener) {
        if (is_array($listener) && is_object($listener[0])) {
            echo '- '.get_class($listener[0])."<BR>";
        }
    }
}

        if (null === $event) {
            $event = new Event();
        }

        $event->setDispatcher($this);
        $event->setName($eventName);

        $this->doDispatch($this->getListeners($eventName), $eventName, $event);
    }

    /**
     * {@inheritDoc}
     */
    public function getListeners($eventName=null) {
        $plisteners = parent::getListeners($eventName);

        $alisteners = array();
        if (null !== $eventName && EventDispatcher::LISTEN_ALL != $eventName) {
            $all = parent::getListeners(EventDispatcher::LISTEN_ALL);
            // universal listener with method name matching
            $method = self::n2m($eventName);
            $tmp = array();
            foreach ($all as $listener) {
                if (is_callable($listener)) {
                    $alisteners[] = $listener;
                } else if (isset($listener->callable) && is_callable($listener->callable)) {
                    $alisteners[] = $listener->callable;
                } else {
                    $methods = get_class_methods($listener);
                    if (in_array($method, $methods)) {
                        $alisteners[] = array($listener, $method);
                    }
                }
            }
        }

        $listeners = array_merge($plisteners, $alisteners);
        return $listeners;
    }

    /**
     * Convert an event name to a method name.
     *
     * <p>Callback method names must follow the following conventions:</p>
     * <ul>
     *  <li>all methods start with the prefix <em>on</em></li>
     *  <li>the reminder of the method name is based on capitalized words of the original event name</li>
     * </ul>
     *
     * <p>For example, to handle the event <em>start_view</em>, the method name would be
     * <code>onStartView(..)</code>.</p>
     *
     * @param string eventName The event name.
     * @return string The method name.
     */
    public static function n2m($eventName) {
        // split into words for ucwords
        $method = str_replace(array('-', '_'), ' ', $eventName);
        // force camel case
        $method = strtolower($method);
        // capitalise words
        $method = ucwords($method);
        // cuddle together :)
        $method = str_replace(' ', '', $method);
        return 'on'.$method;
    }

}
