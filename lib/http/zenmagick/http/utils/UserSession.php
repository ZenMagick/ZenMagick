<?php
/*
 * ZenMagick - Another PHP framework.
 * Copyright (C) 2006-2011 zenmagick.org
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
namespace zenmagick\http\utils;

use zenmagick\base\ZMObject;

/**
 * Flexible container for session data.
 *
 * <p>Using this container to store user data has a number of advantages.</p>
 * <ul>
 *   <li>Data compression if available (<code>gzcompress</code>)</li>
 *   <li>Dynamic serialization of all properties</li>
 *   <li>Can be obtained from the dependency container to have a defined place for object references</li>
 *   <li>Automatically saved before a session is closed</li>
 * </ul>
 *
 * @author DerManoMann <mano@zenmagick.org>
 * @package zenmagick.http.utils
 */
class UserSession extends \ZMObject implements \Serializable {

    /**
     * Serialize this instance.
     */
    public function serialize() {
        $sprops = array();
        foreach ($this->getProperties() as $name => $obj) {
            $sprops[$name] = serialize($obj);
        }

        $serialized = serialize($sprops);

        if (function_exists('gzcompress')) {
            $serialized =  base64_encode(gzcompress($serialized));
        }

        return $serialized;
    }

    /**
     * Unserialize.
     *
     * @param string serialized The serialized data.
     */
    public function unserialize($serialized) {
        if (function_exists('gzcompress')) {
            $serialized = base64_decode(gzuncompress($serialized));
        }

        $sprops = base64_decode(unserialize($serialized));

        foreach ($sprops as $name => $sprop) {
            $this->set($name, unserialize($sprop));
        }
    }
}
