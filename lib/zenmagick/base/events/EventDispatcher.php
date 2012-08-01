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
use zenmagick\base\Runtime;
use zenmagick\base\Toolbox;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\ContainerAwareEventDispatcher;

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
class EventDispatcher extends ContainerAwareEventDispatcher {
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
     *
     * @todo this method matches the parent entirely!
     *       Commenting it out causes our getListeners not to fire though.
     */
    public function dispatch($eventName, Event $event = null) {
        if (null === $event) {
            $event = new Event();
        }

        $event->setDispatcher($this);
        $event->setName($eventName);

        // use hasListeners rather than looking at the private listeners property
        if (!$this->hasListeners($eventName)) {
            return $event;
        }

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
            $method = 'on'.Toolbox::className($eventName);
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
}
