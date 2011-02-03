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
namespace zenmagick\base\events;

use \Symfony\Component\EventDispatcher\EventInterface;

/**
 * The ZenMagick event service.
 *
 * <p>Extends the <em>Symfony event dispatcher</em>, adding:</p>
 * <ul>
 *  <li>The option to listen to <strong>all</strong> events. Reflection is used to determine if a corresponding event callback exists.</li>
 *  <li>Logging of all events.</li>
 * </ul>
 *
 * @author DerManoMann
 * @package zenmagick.base.events
 */
class EventDispatcher extends \Symfony\Component\EventDispatcher\EventDispatcher {
    const LISTEN_ALL = '*';


    /**
     * Listen to all events.
     *
     * @param mixed $listener A PHP object instance or PHP callable.
     * @param integer $priority The priority (between -10 and 10 -- defaults to 0).
     */
    public function listen($listener, $priority=0) {
        $this->connect(EventDispatcher::LISTEN_ALL, $listener, $priority);
    }

    /**
     * {@inheritDoc}
     */
    public function getListeners($name) {
        $listeners = parent::getListeners($name);

        if (isset($this->listeners[EventDispatcher::LISTEN_ALL])) {
            // universal listener with method name matching
            $method = self::n2m($name);
            $all = $this->listeners[EventDispatcher::LISTEN_ALL];
            krsort($all);
            foreach ($all as $pl) {
                $tmp = array();
                // filter and prepare as PHP callable
                foreach ($pl as $l) {
                    if (is_callable($l)) {
                        $tmp[] = $l;
                    } else {
                        $methods = get_class_methods($l);
                        if (in_array($method, $methods)) {
                            $tmp[] = array($l, $method);
                        }
                    }
                }
                $listeners = array_merge($listeners, $tmp);
            }
        }

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
     * @param string name The event name.
     * @return string The method name.
     */
    public static function n2m($name) {
        // split into words for ucwords
        $method = str_replace(array('-', '_'), ' ', $name);
        // capitalise words
        $method = ucwords($method);
        // cuddle together :)
        $method = str_replace(' ', '', $method);
        return 'on'.$method;
    }

}
