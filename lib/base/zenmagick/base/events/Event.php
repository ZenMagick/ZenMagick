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

/**
 * A ZenMagick event service event.
 *
 * @author DerManoMann
 * @package zenmagick.base.events
 */
class Event extends \Symfony\Component\EventDispatcher\Event {
    protected $source;
    protected $parameters;
    private $timestamp;
    private $memory;


    /**
     * Constructs a new Event.
     *
     * @param mixed source The event origin; default is <code>null</code>.
     * @param array parameters An array of parameters; default is an empty <code>array</code>.
     */
    public function __construct($source=null, $parameters=array()) {
        $this->source = $source;
        $this->parameters = $parameters;

        list($u, $s) = explode(' ',microtime());
        $this->timestamp = bcadd($u, $s, 4);
        $this->memory = memory_get_usage(true);
    }

    /**
     * Returns the event origin.
     *
     * @return mixed The source.
     */
    public function getSource() {
        return $this->source;
    }

    /**
     * Returns the event parameters.
     *
     * @return array The event parameters.
     */
    public function all() {
        return $this->parameters;
    }

    /**
     * Returns true if the parameter exists.
     *
     * @param string name The parameter name.
     * @return boolean Return <code>true</code> if the parameter exists, <code>false</code> otherwise.
     */
    public function has($name) {
        return array_key_exists($name, $this->parameters);
    }

    /**
     * Returns a parameter value.
     *
     * @param string nameThe parameter name.
     * @return mixed The parameter value.
     * @throws \InvalidArgumentException When parameter doesn't exists for this event.
     */
    public function get($name) {
        if (!array_key_exists($name, $this->parameters)) {
            throw new \InvalidArgumentException(sprintf('The event "%s" has no "%s" parameter.', $this->name, $name));
        }

        return $this->parameters[$name];
    }

    /**
     * Sets a parameter.
     *
     * @param string name The parameter name.
     * @param mixed value The parameter value.
     */
    public function set($name, $value) {
        $this->parameters[$name] = $value;
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
