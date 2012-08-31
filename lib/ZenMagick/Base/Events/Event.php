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
namespace ZenMagick\base\events;

use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * A ZenMagick event service event.
 *
 * This class adds timestamp and memory usage
 * to each event for profiling.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class Event extends GenericEvent {
    private $timestamp;
    private $memory;


    /**
     * {@inheritDoc}
     */
    public function __construct($subject = null, array $arguments = array()) {
        parent::__construct($subject, $arguments);
        $this->setName('');
        $this->timestamp = microtime(true);
        $this->memory = memory_get_usage(true);
    }

    /**
     * Returns the event parameters.
     *
     * @return array The event parameters.
     */
    public function all() {
        return parent::getArguments();
    }

    /**
     * Returns true if the parameter exists.
     *
     * @param string name The parameter name.
     * @return boolean Return <code>true</code> if the parameter exists, <code>false</code> otherwise.
     */
    public function has($name) {
        return parent::hasArgument($name);
    }

    /**
     * Returns a parameter value.
     *
     * @param string nameThe parameter name.
     * @return mixed The parameter value.
     * @throws \InvalidArgumentException When parameter doesn't exists for this event.
     */
    public function get($name) {
        return parent::getArgument($name);
    }

    /**
     * Sets a parameter.
     *
     * @param string name The parameter name.
     * @param mixed value The parameter value.
     */
    public function set($name, $value) {
        return parent::setArgument($name, $value);
    }

    /**
     * Get the timestamp.
     *
     * @return float The timestamp in seconds.
     */
    public function getTimestamp() {
        return $this->timestamp;
    }

    /**
     * Get the used memory at the time.
     *
     * @return lon The memory usage in bytes.
     */
    public function getMemory() {
        return $this->memory;
    }

}
